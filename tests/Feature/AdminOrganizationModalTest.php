<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use App\Http\Middleware\Authenticate;
use App\Models\Organization;
use App\Support\CardStyles;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Middlewares\RoleMiddleware;
use Tests\TestCase;

class AdminOrganizationModalTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => false,
        ]);
        DB::purge('sqlite');

        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('abbr_en')->nullable();
            $table->string('name_zh')->nullable();
            $table->string('email')->nullable();
            $table->string('avatar')->nullable();
            $table->string('card_style')->nullable();
            $table->boolean('status')->nullable();
            $table->timestamps();
        });
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->boolean('is_organizer')->default(false);
            $table->timestamps();
        });
        Schema::create('organization_user', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('user_id');
        });
        Schema::create('configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->string('key');
            $table->text('value');
        });

        $this->withoutMiddleware([Authenticate::class, RoleMiddleware::class]);
    }

    public function test_create_form_receives_presets(): void
    {
        $this->get(route('admin.organizations.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/OrganizationForm')
                ->where('organization', null)
                ->has('cardStyles', 14)
                ->where('cardStyles.card_01.bg_image', 'card_01.png'));
    }

    public function test_edit_form_receives_organization_and_managers(): void
    {
        DB::table('organizations')->insert([
            'id' => 1,
            'abbr_en' => 'TEST',
            'card_style' => json_encode(['preset' => 'card_01', 'bg_color' => 'navy']),
        ]);
        DB::table('members')->insert([
            ['id' => 10, 'organization_id' => 1, 'user_id' => null, 'is_organizer' => true],
            ['id' => 11, 'organization_id' => 1, 'user_id' => null, 'is_organizer' => false],
        ]);

        $this->get(route('admin.organizations.edit', 1))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/OrganizationForm')
                ->where('organization.id', 1)
                ->where('organization.card_style.bg_color', 'navy')
                ->where('organization.organizer_member_ids', [10])
                ->has('organization.members', 2)
                ->has('cardStyles', 14));
        $this->get(route('admin.organizations.edit', 999))->assertNotFound();
    }

    public function test_create_saves_the_form_fields_and_returns_to_list(): void
    {
        $this->post(route('admin.organizations.store'), [
            'abbr_en' => 'TEST',
            'name_zh' => 'Test organization',
            'email' => 'org@example.com',
            'status' => true,
            'card_style' => null,
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.organizations.index'));

        $this->assertDatabaseHas('organizations', [
            'abbr_en' => 'TEST',
            'name_zh' => 'Test organization',
            'email' => 'org@example.com',
            'status' => 1,
        ]);
    }

    public function test_card_style_preset_and_custom_values_are_saved_and_can_be_updated(): void
    {
        $style = [
            'preset' => 'card_01',
            'name' => 'Card 01',
            'bg_image' => 'card_01.png',
            'logo' => '/logos/custom.png',
            'bg_color' => '#123456',
            'font_color' => '#ffffff',
            'font_style' => 'font-family:sans-serif;color:white',
        ];

        $this->post(route('admin.organizations.store'), [
            'abbr_en' => 'TEST',
            'name_zh' => 'Test organization',
            'email' => 'org@example.com',
            'card_style' => $style,
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.organizations.index'));

        $organization = Organization::firstOrFail();
        $this->assertSame($style, $organization->card_style);

        $style['bg_color'] = 'navy';
        $style['logo'] = '/logos/updated.png';
        $style['bg_image'] = 'custom.png';
        $style['font_style'] = 'font-family:serif;color:red';
        $this->patch(route('admin.organizations.update', $organization->id), [
            'abbr_en' => 'TEST',
            'card_style' => $style,
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.organizations.index'));
        $this->assertSame($style, $organization->fresh()->card_style);
    }

    public function test_invalid_submission_returns_to_form_without_saving(): void
    {
        $this->from(route('admin.organizations.create'))
            ->post(route('admin.organizations.store'), ['abbr_en' => ''])
            ->assertRedirect(route('admin.organizations.create'))
            ->assertSessionHasErrors(['abbr_en', 'name_zh', 'email']);

        $this->assertDatabaseCount('organizations', 0);
    }

    public function test_images_are_uploaded_replaced_and_removed_on_save(): void
    {
        Storage::fake('public');
        $this->post(route('admin.organizations.store'), [
            'abbr_en' => 'TEST',
            'name_zh' => 'Test organization',
            'email' => 'org@example.com',
            'card_style' => ['preset' => 'card_01', 'logo' => '/storage/images/site_logo.png', 'bg_image' => 'card_01.png'],
            'card_style_logo_upload' => UploadedFile::fake()->image('logo.png'),
            'card_style_bg_image_upload' => UploadedFile::fake()->image('background.jpg'),
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.organizations.index'));

        $organization = Organization::firstOrFail();
        $style = $organization->card_style;
        $logo = substr($style['logo'], strlen('/storage/'));
        $background = substr($style['bg_image'], strlen('/storage/'));
        Storage::disk('public')->assertExists([$logo, $background]);

        $this->post(route('admin.organizations.update', $organization->id), [
            '_method' => 'PATCH',
            'abbr_en' => 'TEST',
            'card_style' => $style,
            'card_style_logo_upload' => UploadedFile::fake()->image('replacement.png'),
            'organizer_member_ids_present' => true,
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.organizations.index'));

        $style = $organization->fresh()->card_style;
        $replacement = substr($style['logo'], strlen('/storage/'));
        Storage::disk('public')->assertMissing($logo);
        Storage::disk('public')->assertExists([$replacement, $background]);

        $style['logo'] = null;
        $style['bg_image'] = null;
        $this->patch(route('admin.organizations.update', $organization->id), [
            'abbr_en' => 'TEST',
            'card_style' => $style,
        ])->assertSessionHasNoErrors();
        $this->assertNull($organization->fresh()->card_style['logo']);
        $this->assertNull($organization->fresh()->card_style['bg_image']);
        Storage::disk('public')->assertMissing([$replacement, $background]);
    }

    public function test_organization_logo_and_card_images_are_separate_and_use_the_same_folder(): void
    {
        Storage::fake('public');
        $this->post(route('admin.organizations.store'), [
            'abbr_en' => 'TEST',
            'name_zh' => 'Test organization',
            'email' => 'org@example.com',
            'card_style' => ['preset' => 'card_01'],
            'avatar_upload' => UploadedFile::fake()->image('site.png'),
            'card_style_logo_upload' => UploadedFile::fake()->image('card.jpg'),
            'card_style_bg_image_upload' => UploadedFile::fake()->image('background.png'),
        ])->assertSessionHasNoErrors();

        $organization = Organization::firstOrFail();
        $style = $organization->card_style;
        $prefix = '~^/storage/organizations/' . $organization->id . '/[a-zA-Z0-9]{40}';
        $this->assertMatchesRegularExpression($prefix . '\.png$~', $organization->avatar);
        $this->assertMatchesRegularExpression($prefix . '\.jpg$~', $style['logo']);
        $this->assertMatchesRegularExpression($prefix . '\.png$~', $style['bg_image']);
        $this->assertCount(3, array_unique([$organization->avatar, $style['logo'], $style['bg_image']]));
        $oldAvatar = substr($organization->avatar, strlen('/storage/'));
        $cardLogo = substr($style['logo'], strlen('/storage/'));
        $background = substr($style['bg_image'], strlen('/storage/'));
        Storage::disk('public')->assertExists([$oldAvatar, $cardLogo, $background]);

        $this->post(route('admin.organizations.update', $organization->id), [
            '_method' => 'PATCH',
            'abbr_en' => 'TEST',
            'avatar' => $organization->avatar,
            'avatar_upload' => UploadedFile::fake()->image('new.jpg'),
        ])->assertSessionHasNoErrors();
        $organization->refresh();
        $this->assertMatchesRegularExpression($prefix . '\.jpg$~', $organization->avatar);
        $this->assertSame($style, $organization->card_style);
        Storage::disk('public')->assertMissing($oldAvatar);
        Storage::disk('public')->assertExists([substr($organization->avatar, strlen('/storage/')), $cardLogo, $background]);

        $replacement = substr($organization->avatar, strlen('/storage/'));
        $this->patch(route('admin.organizations.update', $organization->id), [
            'abbr_en' => 'TEST',
            'avatar' => null,
        ])->assertSessionHasNoErrors();
        $this->assertNull($organization->fresh()->avatar);
        Storage::disk('public')->assertMissing($replacement);
        Storage::disk('public')->assertExists([$cardLogo, $background]);
    }

    public function test_organization_logo_upload_obeys_image_restrictions(): void
    {
        Storage::fake('public');
        $this->post(route('admin.organizations.store'), [
            'abbr_en' => 'TEST',
            'name_zh' => 'Test organization',
            'email' => 'org@example.com',
            'avatar_upload' => UploadedFile::fake()->image('logo.gif'),
        ])->assertSessionHasErrors('avatar_upload');

        $oversized = UploadedFile::fake()->image('logo.png');
        $oversized->sizeToReport = 3 * 1024 * 1024 + 1;
        $this->post(route('admin.organizations.store'), [
            'abbr_en' => 'TEST',
            'name_zh' => 'Test organization',
            'email' => 'org@example.com',
            'avatar_upload' => $oversized,
        ])->assertSessionHasErrors('avatar_upload');
        $this->assertDatabaseCount('organizations', 0);
    }

    public function test_uploading_to_existing_organization_uses_its_id_folder(): void
    {
        Storage::fake('public');
        DB::table('organizations')->insert(['id' => 1, 'abbr_en' => 'TEST']);
        $this->patch(route('admin.organizations.update', 1), [
            'abbr_en' => 'TEST',
            'avatar_upload' => UploadedFile::fake()->image('site.png'),
        ])->assertSessionHasNoErrors();
        $organization = Organization::findOrFail(1);
        $avatar = $organization->avatar;

        $this->patch(route('admin.organizations.update', 1), [
            'abbr_en' => 'TEST',
            'card_style' => ['preset' => 'card_01'],
            'card_style_logo_upload' => UploadedFile::fake()->image('card.png'),
        ])->assertSessionHasNoErrors();
        $organization->refresh();
        $this->assertSame($avatar, $organization->avatar);
        $this->assertMatchesRegularExpression('~^/storage/organizations/1/[a-zA-Z0-9]{40}\.png$~', $organization->card_style['logo']);
    }

    public function test_removing_main_logo_preserves_the_file_if_other_organization_uses_it(): void
    {
        Storage::fake('public');
        $path = 'organizations/1/' . str_repeat('a', 40) . '.png';
        Storage::disk('public')->put($path, 'shared');
        DB::table('organizations')->insert([
            ['id' => 1, 'abbr_en' => 'ONE', 'avatar' => '/storage/' . $path],
            ['id' => 2, 'abbr_en' => 'TWO', 'avatar' => '/storage/' . $path],
        ]);
        $this->patch(route('admin.organizations.update', 1), [
            'abbr_en' => 'ONE',
            'avatar' => null,
        ])->assertSessionHasNoErrors();
        Storage::disk('public')->assertExists($path);
    }

    public function test_replacing_a_legacy_image_stores_the_new_image_in_the_id_folder(): void
    {
        Storage::fake('public');
        $oldLogo = 'organizations/1123456/site_logo/' . str_repeat('a', 40) . '.png';
        $oldCardLogo = 'organization-card-styles/1/logo/' . str_repeat('b', 40) . '.png';
        $oldBackground = 'organizations/1123456/card_background/' . str_repeat('c', 40) . '.png';
        foreach ([$oldLogo, $oldCardLogo, $oldBackground] as $path) {
            Storage::disk('public')->put($path, 'old');
        }
        DB::table('organizations')->insert([
            'id' => 1,
            'abbr_en' => 'TEST',
            'avatar' => '/storage/' . $oldLogo,
            'card_style' => json_encode(['logo' => '/storage/' . $oldCardLogo, 'bg_image' => '/storage/' . $oldBackground]),
        ]);
        $this->patch(route('admin.organizations.update', 1), [
            'abbr_en' => 'TEST',
            'card_style' => ['logo' => '/storage/' . $oldCardLogo, 'bg_image' => '/storage/' . $oldBackground],
            'avatar_upload' => UploadedFile::fake()->image('site.png'),
            'card_style_logo_upload' => UploadedFile::fake()->image('card.png'),
        ])->assertSessionHasNoErrors();
        $organization = Organization::findOrFail(1);
        $this->assertMatchesRegularExpression('~^/storage/organizations/1/[a-zA-Z0-9]{40}\.png$~', $organization->avatar);
        $this->assertMatchesRegularExpression('~^/storage/organizations/1/[a-zA-Z0-9]{40}\.png$~', $organization->card_style['logo']);
        $this->assertSame('/storage/' . $oldBackground, $organization->card_style['bg_image']);
        Storage::disk('public')->assertMissing([$oldLogo, $oldCardLogo]);
        Storage::disk('public')->assertExists([
            $oldBackground,
            substr($organization->avatar, strlen('/storage/')),
            substr($organization->card_style['logo'], strlen('/storage/')),
        ]);
    }

    public function test_removing_an_image_does_not_delete_files_belonging_to_another_organization(): void
    {
        Storage::fake('public');
        $path = 'organizations/2/' . str_repeat('a', 40) . '.png';
        Storage::disk('public')->put($path, 'other');
        DB::table('organizations')->insert(['id' => 1, 'abbr_en' => 'ONE', 'avatar' => '/storage/' . $path]);
        $this->patch(route('admin.organizations.update', 1), [
            'abbr_en' => 'ONE',
            'avatar' => null,
        ])->assertSessionHasNoErrors();
        Storage::disk('public')->assertExists($path);
    }

    public function test_replacing_singular_directory_upload_preserves_other_images(): void
    {
        Storage::fake('public');
        $oldLogo = 'organization/1/' . str_repeat('a', 40) . '.png';
        $background = 'organization/1/' . str_repeat('b', 40) . '.png';
        Storage::disk('public')->put($oldLogo, 'old logo');
        Storage::disk('public')->put($background, 'background');
        DB::table('organizations')->insert([
            'id' => 1,
            'abbr_en' => 'TEST',
            'avatar' => '/storage/' . $oldLogo,
            'card_style' => json_encode(['bg_image' => '/storage/' . $background]),
        ]);
        $this->patch(route('admin.organizations.update', 1), [
            'abbr_en' => 'TEST',
            'avatar_upload' => UploadedFile::fake()->image('replacement.jpg'),
        ])->assertSessionHasNoErrors();

        $organization = Organization::findOrFail(1);
        $this->assertMatchesRegularExpression('~^/storage/organizations/1/[a-zA-Z0-9]{40}\.jpg$~', $organization->avatar);
        $this->assertSame('/storage/' . $background, $organization->card_style['bg_image']);
        Storage::disk('public')->assertMissing($oldLogo);
        Storage::disk('public')->assertExists([$background, substr($organization->avatar, strlen('/storage/'))]);
    }

    public function test_invalid_upload_does_not_change_style_or_delete_existing_images(): void
    {
        Storage::fake('public');
        $path = 'organization-card-styles/1/logo/' . str_repeat('a', 40) . '.png';
        Storage::disk('public')->put($path, 'existing');
        DB::table('organizations')->insert([
            'id' => 1,
            'abbr_en' => 'TEST',
            'card_style' => json_encode(['logo' => '/storage/' . $path]),
        ]);
        $this->patch(route('admin.organizations.update', 1), [
            'abbr_en' => 'TEST',
            'card_style' => ['logo' => null],
            'card_style_logo_upload' => UploadedFile::fake()->create('invalid.txt', 1, 'text/plain'),
        ])->assertSessionHasErrors('card_style_logo_upload');

        $this->assertSame('/storage/' . $path, Organization::findOrFail(1)->card_style['logo']);
        Storage::disk('public')->assertExists($path);
    }

    public function test_card_images_accept_jpg_and_png_up_to_three_megabytes(): void
    {
        Storage::fake('public');
        $this->post(route('admin.organizations.store'), [
            'abbr_en' => 'TEST',
            'name_zh' => 'Test organization',
            'email' => 'org@example.com',
            'card_style' => ['preset' => 'card_01'],
            'card_style_logo_upload' => UploadedFile::fake()->image('logo.jpg')->size(3072),
            'card_style_bg_image_upload' => UploadedFile::fake()->image('background.png')->size(3072),
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('organizations', 1);
        $style = Organization::firstOrFail()->card_style;
        Storage::disk('public')->assertExists([
            substr($style['logo'], strlen('/storage/')),
            substr($style['bg_image'], strlen('/storage/')),
        ]);
    }

    public function test_card_images_reject_gif_and_files_larger_than_three_megabytes(): void
    {
        Storage::fake('public');
        $oversizedBackground = UploadedFile::fake()->image('background.png');
        $oversizedBackground->sizeToReport = 3 * 1024 * 1024 + 1;
        $this->post(route('admin.organizations.store'), [
            'abbr_en' => 'TEST',
            'name_zh' => 'Test organization',
            'email' => 'org@example.com',
            'card_style' => ['preset' => 'card_01'],
            'card_style_logo_upload' => UploadedFile::fake()->image('logo.gif'),
            'card_style_bg_image_upload' => $oversizedBackground,
        ])->assertSessionHasErrors(['card_style_logo_upload', 'card_style_bg_image_upload']);
        $this->assertDatabaseCount('organizations', 0);

        $oversizedLogo = UploadedFile::fake()->image('logo.png');
        $oversizedLogo->sizeToReport = 3 * 1024 * 1024 + 1;
        $this->post(route('admin.organizations.store'), [
            'abbr_en' => 'TEST',
            'name_zh' => 'Test organization',
            'email' => 'org@example.com',
            'card_style' => ['preset' => 'card_01'],
            'card_style_logo_upload' => $oversizedLogo,
            'card_style_bg_image_upload' => UploadedFile::fake()->image('background.gif'),
        ])->assertSessionHasErrors(['card_style_logo_upload', 'card_style_bg_image_upload']);
        $this->assertDatabaseCount('organizations', 0);
    }

    public function test_update_rejects_oversized_and_non_jpg_or_png_images_without_changing_the_card(): void
    {
        Storage::fake('public');
        $style = ['logo' => '/storage/images/site_logo.png', 'bg_image' => 'card_01.png'];
        DB::table('organizations')->insert([
            'id' => 1,
            'abbr_en' => 'TEST',
            'card_style' => json_encode($style),
        ]);
        $oversized = UploadedFile::fake()->image('logo.jpg');
        $oversized->sizeToReport = 3 * 1024 * 1024 + 1;

        $this->post(route('admin.organizations.update', 1), [
            '_method' => 'PATCH',
            'abbr_en' => 'TEST',
            'card_style' => $style,
            'card_style_logo_upload' => $oversized,
            'card_style_bg_image_upload' => UploadedFile::fake()->image('background.gif'),
        ])->assertSessionHasErrors(['card_style_logo_upload', 'card_style_bg_image_upload']);

        $this->assertSame($style, Organization::findOrFail(1)->card_style);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_removing_shared_preset_images_does_not_delete_them(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('images/site_logo.png', 'shared');
        Storage::disk('public')->put('images/card_01.png', 'shared');
        DB::table('organizations')->insert([
            'id' => 1, 'abbr_en' => 'TEST',
            'card_style' => json_encode(['logo' => '/storage/images/site_logo.png', 'bg_image' => 'card_01.png']),
        ]);
        $this->patch(route('admin.organizations.update', 1), [
            'abbr_en' => 'TEST',
            'card_style' => ['logo' => null, 'bg_image' => null],
        ])->assertSessionHasNoErrors();
        Storage::disk('public')->assertExists(['images/site_logo.png', 'images/card_01.png']);
    }

    public function test_removing_an_image_referenced_by_another_organization_keeps_the_file(): void
    {
        Storage::fake('public');
        $path = 'organization-card-styles/1/logo/' . str_repeat('a', 40) . '.png';
        Storage::disk('public')->put($path, 'shared');
        DB::table('organizations')->insert([
            ['id' => 1, 'abbr_en' => 'ONE', 'card_style' => json_encode(['logo' => '/storage/' . $path])],
            ['id' => 2, 'abbr_en' => 'TWO', 'card_style' => json_encode(['bg_image' => '/storage/' . $path])],
        ]);
        $this->patch(route('admin.organizations.update', 1), [
            'abbr_en' => 'ONE',
            'card_style' => ['logo' => null],
        ])->assertSessionHasNoErrors();
        Storage::disk('public')->assertExists($path);
    }

    public function test_multipart_update_can_clear_managers(): void
    {
        DB::table('organizations')->insert(['id' => 1, 'abbr_en' => 'TEST']);
        DB::table('members')->insert(['id' => 10, 'organization_id' => 1, 'is_organizer' => true]);
        $this->post(route('admin.organizations.update', 1), [
            '_method' => 'PATCH',
            'abbr_en' => 'TEST',
            'organizer_member_ids_present' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('members', ['id' => 10, 'is_organizer' => false]);
    }

    public function test_default_presets_match_the_requested_values_and_keep_extra_configured_styles(): void
    {
        DB::table('configs')->insert([
            'organization_id' => 0,
            'key' => 'card_styles',
            'value' => json_encode([
                'card_01' => ['name' => 'Old'],
                'custom' => ['name' => 'Custom'],
            ]),
        ]);
        $styles = CardStyles::presets();
        $this->assertCount(15, $styles);
        $this->assertSame(['name' => 'Custom'], $styles['custom']);

        for ($number = 1; $number <= 14; $number++) {
            $id = sprintf('%02d', $number);
            $this->assertSame([
                'name' => "Card {$id}",
                'bg_image' => sprintf('card_%02d.png', min($number, 3)),
                'logo' => '/storage/images/site_logo.png',
                'bg_color' => 'blue',
                'font_color' => 'white',
                'font_style' => 'font-family:sans-serif;color:' . ($number === 1 ? '#c24641' : 'white'),
            ], $styles["card_{$id}"]);
        }
    }

    public function test_organization_page_receives_default_presets(): void
    {
        $this->get(route('admin.organizations.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Admin/Organizations')
                ->has('cardStyles', 14)
                ->where('cardStyles.card_01.bg_image', 'card_01.png')
                ->where('cardStyles.card_01.font_color', 'white')
                ->where('cardStyles.card_14.bg_image', 'card_03.png'));
    }

    public function test_legacy_style_identifiers_remain_readable_and_updatable(): void
    {
        DB::table('organizations')->insert(['id' => 1, 'abbr_en' => 'OLD', 'card_style' => 'card_01']);
        $this->assertSame('card_01', Organization::findOrFail(1)->card_style);

        $this->patch(route('admin.organizations.update', 1), [
            'abbr_en' => 'OLD',
            'card_style' => 'card_02',
        ])->assertSessionHasNoErrors();
        $this->assertSame('card_02', Organization::findOrFail(1)->card_style);
    }

    public function test_invalid_card_style_is_rejected(): void
    {
        DB::table('organizations')->insert(['id' => 1, 'abbr_en' => 'OLD', 'card_style' => 'card_01']);

        $this->patch(route('admin.organizations.update', 1), [
            'abbr_en' => 'OLD',
            'card_style' => ['bg_color' => ['invalid']],
        ])->assertSessionHasErrors('card_style.bg_color');

        $this->assertSame('card_01', Organization::findOrFail(1)->card_style);
    }

    public function test_edit_updates_member_managers_without_changing_user_links(): void
    {
        DB::table('organizations')->insert(['id' => 1, 'abbr_en' => 'OLD']);
        DB::table('members')->insert([
            ['id' => 10, 'organization_id' => 1, 'user_id' => 100, 'is_organizer' => true],
            ['id' => 11, 'organization_id' => 1, 'user_id' => 101, 'is_organizer' => false],
        ]);
        DB::table('organization_user')->insert(['organization_id' => 1, 'user_id' => 100]);

        $this->patch(route('admin.organizations.update', 1), [
            'abbr_en' => 'NEW',
            'status' => false,
            'organizer_member_ids' => [11],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('organizations', ['id' => 1, 'abbr_en' => 'NEW', 'status' => 0]);
        $this->assertDatabaseHas('members', ['id' => 10, 'is_organizer' => 0]);
        $this->assertDatabaseHas('members', ['id' => 11, 'is_organizer' => 1]);
        $this->assertDatabaseHas('organization_user', ['organization_id' => 1, 'user_id' => 100]);
        $this->assertDatabaseCount('organization_user', 1);
    }

    public function test_member_from_another_organization_is_rejected(): void
    {
        DB::table('organizations')->insert(['id' => 1, 'abbr_en' => 'OLD']);
        DB::table('members')->insert([
            ['id' => 10, 'organization_id' => 1, 'is_organizer' => true],
            ['id' => 11, 'organization_id' => 2, 'is_organizer' => false],
        ]);

        $this->patch(route('admin.organizations.update', 1), [
            'abbr_en' => 'NEW',
            'organizer_member_ids' => [11],
        ])->assertSessionHasErrors('organizer_member_ids.0');

        $this->assertDatabaseHas('organizations', ['id' => 1, 'abbr_en' => 'OLD']);
        $this->assertDatabaseHas('members', ['id' => 10, 'is_organizer' => 1]);
        $this->assertDatabaseHas('members', ['id' => 11, 'is_organizer' => 0]);
    }

    public function test_omitting_managers_preserves_existing_assignments(): void
    {
        DB::table('organizations')->insert(['id' => 1, 'abbr_en' => 'OLD']);
        DB::table('members')->insert(['id' => 10, 'organization_id' => 1, 'is_organizer' => true]);

        $this->patch(route('admin.organizations.update', 1), ['abbr_en' => 'NEW'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('members', ['id' => 10, 'is_organizer' => 1]);
    }
}
