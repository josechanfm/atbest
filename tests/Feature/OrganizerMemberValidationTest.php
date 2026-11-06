<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Http\Middleware\Authenticate;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OrganizerMemberValidationTest extends TestCase
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
            $table->string('display_name')->nullable();
            $table->char('gender', 1)->nullable();
            $table->date('dob')->nullable();
            $table->timestamps();
        });

        $this->withoutMiddleware([Authenticate::class, Authorize::class]);
    }

    public function test_create_and_update_require_display_name(): void
    {
        DB::table('members')->insert(['id' => 1, 'display_name' => 'Original']);

        $this->post(route('organizer.members.store'), [])
            ->assertSessionHasErrors('display_name');
        $this->patch(route('organizer.members.update', 1), ['display_name' => '   '])
            ->assertSessionHasErrors('display_name');

        $this->assertDatabaseCount('members', 1);
        $this->assertDatabaseHas('members', ['id' => 1, 'display_name' => 'Original']);
    }

    public function test_display_name_can_be_saved_without_date_of_birth_or_gender(): void
    {
        $organization = new Organization();
        $organization->id = 1;

        $this->withSession(['organization' => $organization])
            ->post(route('organizer.members.store'), ['display_name' => 'New Member'])
            ->assertSessionHasNoErrors();

        $this->patch(route('organizer.members.update', 1), ['display_name' => 'Updated Member'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('members', [
            'id' => 1,
            'display_name' => 'Updated Member',
            'dob' => null,
            'gender' => null,
        ]);
    }
}
