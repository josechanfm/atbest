<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MemberDashboardTest extends TestCase
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
            $table->string('card_style')->nullable();
        });
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('organization_id');
            $table->boolean('is_default')->default(false);
        });
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->integer('sequence');
        });
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->string('category_code');
            $table->boolean('published');
            $table->integer('sequence');
        });
        Schema::create('configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->string('key');
            $table->text('value');
        });
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->integer('order_column')->nullable();
        });

        DB::table('organizations')->insert([
            ['id' => 1, 'abbr_en' => 'FIRST', 'card_style' => 'custom'],
            ['id' => 2, 'abbr_en' => 'SECOND', 'card_style' => 'other'],
        ]);
        DB::table('members')->insert([
            ['id' => 1, 'user_id' => 1, 'organization_id' => 1, 'is_default' => true],
            ['id' => 2, 'user_id' => 1, 'organization_id' => 2, 'is_default' => false],
        ]);
        DB::table('features')->insert([
            ['id' => 1, 'organization_id' => 1, 'sequence' => 2],
            ['id' => 2, 'organization_id' => 1, 'sequence' => 1],
            ['id' => 3, 'organization_id' => 2, 'sequence' => 1],
            ['id' => 4, 'organization_id' => 1, 'sequence' => 3],
            ['id' => 5, 'organization_id' => 1, 'sequence' => 4],
            ['id' => 6, 'organization_id' => 1, 'sequence' => 5],
        ]);
        DB::table('articles')->insert([
            ['id' => 1, 'organization_id' => 1, 'category_code' => 'NEWS', 'published' => true, 'sequence' => 1],
            ['id' => 2, 'organization_id' => 1, 'category_code' => 'NEWS', 'published' => true, 'sequence' => 2],
            ['id' => 3, 'organization_id' => 1, 'category_code' => 'NEWS', 'published' => false, 'sequence' => 3],
            ['id' => 4, 'organization_id' => 1, 'category_code' => 'OTHER', 'published' => true, 'sequence' => 4],
            ['id' => 5, 'organization_id' => 2, 'category_code' => 'NEWS', 'published' => true, 'sequence' => 5],
        ]);
        DB::table('configs')->insert([
            'organization_id' => 1,
            'key' => 'card_styles',
            'value' => json_encode(['custom' => 'custom-card']),
        ]);
    }

    private function login(?Member $member, bool $admin = false): void
    {
        $user = new User();
        $user->id = 1;
        $user->setRelation('member', $member);
        $user->setRelation('roles', $admin ? collect([new Role(['name' => 'admin'])]) : collect());
        $this->actingAs($user);
    }

    public function test_dashboard_initializes_session_and_filters_organization_data(): void
    {
        $member = Member::findOrFail(1);
        $this->login($member);

        $response = $this->get(route('member.dashboard'));

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Member/Dashboard')
            ->where('member.id', 1)
            ->where('member.name_card_link', $member->nameCardLink())
            ->has('members', 2)
            ->has('features', 4)
            ->where('features.0.id', 2)
            ->where('features.1.id', 1)
            ->where('features.2.id', 4)
            ->where('features.3.id', 5)
            ->has('articles', 2)
            ->where('articles.0.id', 2)
            ->where('articles.1.id', 1)
            ->where('cardStyle', 'custom-card'));
        $response->assertSessionHas('member', fn ($value) => $value->id === 1);
        $response->assertSessionHas('organization', fn ($value) => $value->id === 1);
    }

    public function test_dashboard_preserves_existing_member_session(): void
    {
        $this->login(Member::findOrFail(1));
        $existing = Member::findOrFail(2);

        $response = $this->withSession([
            'member' => $existing,
            'organization' => $existing->organization,
        ])->get(route('member.dashboard'));

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('member.id', 1)
            ->has('features', 1)
            ->where('features.0.id', 3)
            ->has('articles', 1)
            ->where('articles.0.id', 5)
            ->where('cardStyle', 'card_01'));
        $response->assertSessionHas('member', fn ($value) => $value->id === 2);
        $response->assertSessionHas('organization', fn ($value) => $value->id === 2);
    }

    public function test_admin_and_users_without_a_member_are_redirected(): void
    {
        $this->login(null, true);
        $this->get(route('member.dashboard'))->assertRedirect('/admin');

        $this->login(null);
        $this->get(route('member.dashboard'))->assertRedirect(route('guest.dashboard'));
    }

    public function test_dashboard_accepts_custom_card_style_objects(): void
    {
        $style = ['logo' => '/custom.png', 'bg_color' => '#123456', 'font_color' => '#fff'];
        DB::table('organizations')->where('id', 1)->update(['card_style' => json_encode($style)]);
        $this->login(Member::findOrFail(1));

        $this->get(route('member.dashboard'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('member.organization.card_style', $style)
            ->where('cardStyle', $style));
    }
}
