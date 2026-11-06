<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MembershipSwitchTest extends TestCase
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
            $table->string('name_zh');
        });
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('organization_id');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        DB::table('organizations')->insert([
            ['id' => 1, 'name_zh' => 'First'],
            ['id' => 2, 'name_zh' => 'Second'],
            ['id' => 3, 'name_zh' => 'Other'],
        ]);
        DB::table('members')->insert([
            ['id' => 1, 'user_id' => 1, 'organization_id' => 1, 'is_default' => true],
            ['id' => 2, 'user_id' => 1, 'organization_id' => 2, 'is_default' => false],
            ['id' => 3, 'user_id' => 2, 'organization_id' => 3, 'is_default' => true],
        ]);

        $user = new User();
        $user->id = 1;
        $this->actingAs($user);
    }

    public function test_switch_updates_default_and_session_member_and_organization(): void
    {
        $oldMember = Member::findOrFail(1);

        $response = $this->withSession([
            'member' => $oldMember,
            'organization' => $oldMember->organization,
        ])->post(route('member.membership.switch', 2));

        $response->assertRedirect(route('member.dashboard'));
        $this->assertDatabaseHas('members', ['id' => 1, 'is_default' => false]);
        $this->assertDatabaseHas('members', ['id' => 2, 'is_default' => true]);
        $this->assertDatabaseHas('members', ['id' => 3, 'is_default' => true]);
        $response->assertSessionHas('member', function ($member) {
            return $member->id === 2 && $member->is_default && $member->organization->id === 2;
        });
        $response->assertSessionHas('organization', fn ($organization) => $organization->id === 2);
    }

    public function test_switch_without_existing_session_sets_member_and_organization(): void
    {
        $response = $this->post(route('member.membership.switch', 2));

        $response->assertRedirect(route('member.dashboard'));
        $response->assertSessionHas('member', fn ($member) => $member->id === 2);
        $response->assertSessionHas('organization', fn ($organization) => $organization->id === 2);
    }

    public function test_switching_to_current_default_keeps_it_default(): void
    {
        $response = $this->post(route('member.membership.switch', 1));

        $response->assertRedirect(route('member.dashboard'));
        $this->assertDatabaseHas('members', ['id' => 1, 'is_default' => true]);
        $this->assertDatabaseHas('members', ['id' => 2, 'is_default' => false]);
        $response->assertSessionHas('member', fn ($member) => $member->id === 1 && $member->is_default);
        $response->assertSessionHas('organization', fn ($organization) => $organization->id === 1);
    }

    public function test_switch_rejects_another_users_membership(): void
    {
        $oldMember = Member::findOrFail(1);

        $response = $this->withSession([
            'member' => $oldMember,
            'organization' => $oldMember->organization,
        ])->post(route('member.membership.switch', 3));

        $response->assertForbidden();
        $this->assertDatabaseHas('members', ['id' => 1, 'is_default' => true]);
        $this->assertDatabaseHas('members', ['id' => 2, 'is_default' => false]);
        $this->assertDatabaseHas('members', ['id' => 3, 'is_default' => true]);
        $response->assertSessionHas('member', fn ($member) => $member->id === 1);
        $response->assertSessionHas('organization', fn ($organization) => $organization->id === 1);
    }
}
