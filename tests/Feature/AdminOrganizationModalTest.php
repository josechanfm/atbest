<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use App\Http\Middleware\Authenticate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        $this->withoutMiddleware([Authenticate::class, RoleMiddleware::class]);
    }

    public function test_create_saves_the_modal_fields(): void
    {
        $this->post(route('admin.organizations.store'), [
            'abbr_en' => 'TEST',
            'name_zh' => 'Test organization',
            'email' => 'org@example.com',
            'status' => true,
            'card_style' => null,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('organizations', [
            'abbr_en' => 'TEST',
            'name_zh' => 'Test organization',
            'email' => 'org@example.com',
            'status' => 1,
        ]);
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
