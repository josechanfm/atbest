<?php

namespace Tests\Feature;

use App\Http\Middleware\Authenticate;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Middlewares\RoleMiddleware;
use Tests\TestCase;

class AdminMemberValidationTest extends TestCase
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
        });
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->string('given_name')->nullable();
            $table->string('family_name')->nullable();
            $table->string('display_name')->nullable();
            $table->string('email')->nullable();
            $table->date('valid_at')->nullable();
            $table->boolean('is_organizer')->default(false);
            $table->timestamps();
        });

        $this->withoutMiddleware([Authenticate::class, RoleMiddleware::class]);
        DB::table('organizations')->insert(['id' => 1]);
    }

    public function test_create_requires_all_member_fields(): void
    {
        $this->post(route('admin.members.store'), [])
            ->assertSessionHasErrors(['organization_id', 'given_name', 'family_name', 'display_name', 'email']);

        $this->assertDatabaseCount('members', 0);
    }

    public function test_update_requires_all_member_fields_and_preserves_existing_data(): void
    {
        DB::table('members')->insert(['id' => 1, 'given_name' => 'Existing']);

        $this->patch(route('admin.members.update', 1), [
            'organization_id' => null,
            'given_name' => ' ',
            'family_name' => '',
            'display_name' => '',
            'email' => '',
        ])->assertSessionHasErrors(['organization_id', 'given_name', 'family_name', 'display_name', 'email']);

        $this->assertDatabaseHas('members', ['id' => 1, 'given_name' => 'Existing']);
    }

    public function test_create_and_update_save_required_and_optional_fields(): void
    {
        $member = [
            'organization_id' => 1,
            'given_name' => 'Jane',
            'family_name' => 'Doe',
            'display_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'valid_at' => '2026-01-01',
            'is_organizer' => true,
        ];

        $this->post(route('admin.members.store'), $member)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('members', $member);

        $member['display_name'] = 'Jane D';
        $member['is_organizer'] = false;
        $this->patch(route('admin.members.update', 1), $member)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('members', $member);
    }
}
