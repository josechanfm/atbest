<?php

namespace Tests\Feature;

use App\Http\Middleware\Authenticate;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Middlewares\RoleMiddleware;
use Tests\TestCase;

class AdminMemberPaginationTest extends TestCase
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
            $table->boolean('status')->default(true);
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id();
        });
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('given_name')->nullable();
            $table->string('family_name')->nullable();
            $table->string('avatar')->nullable();
        });
        DB::table('organizations')->insert([['id' => 1], ['id' => 2]]);
        DB::table('members')->insert([
            ['id' => 1, 'organization_id' => 1, 'given_name' => 'Jane', 'family_name' => 'Doe'],
            ['id' => 2, 'organization_id' => 1, 'given_name' => 'Janet', 'family_name' => 'Doe'],
            ['id' => 3, 'organization_id' => 1, 'given_name' => 'Janice', 'family_name' => 'Doe'],
            ['id' => 4, 'organization_id' => 2, 'given_name' => 'Jane', 'family_name' => 'Doe'],
        ]);

        $this->withoutMiddleware([Authenticate::class, RoleMiddleware::class]);
    }

    public function test_table_receives_filtered_second_page_and_page_size(): void
    {
        $response = $this->get(route('admin.members.index', [
            'page' => 2,
            'per_page' => 2,
            'search' => ['organization' => 1, 'given_name' => 'Jan', 'family_name' => 'Doe'],
        ]));

        $response->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Members')
            ->where('members.total', 3)
            ->where('members.current_page', 2)
            ->where('members.per_page', 2)
            ->has('members.data', 1)
            ->where('members.data.0.id', 3)
            ->where('filters.organization', '1')
            ->where('filters.given_name', 'Jan')
            ->where('filters.family_name', 'Doe'));
    }
}
