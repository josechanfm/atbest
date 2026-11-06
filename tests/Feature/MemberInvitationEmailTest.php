<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MemberInvitationEmailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => false,
            'services.genie_email.base_url' => 'https://gateway.example',
            'services.genie_email.abbr' => 'test',
            'services.genie_email.from' => 'sender@example.com',
        ]);
        DB::purge('sqlite');

        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->unsignedBigInteger('inviter_id');
            $table->string('email')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->unsignedBigInteger('used_by')->nullable();
            $table->timestamps();
        });

        $user = new User();
        $user->id = 42;
        $this->actingAs($user);
    }

    public function test_email_invitation_is_saved_and_sent(): void
    {
        Http::fake(['gateway.example/*' => Http::response('', 200)]);

        $response = $this->from(route('member.invitations.create'))
            ->post(route('member.invitations.store'), ['email' => 'invitee@example.com']);

        $response->assertRedirect(route('member.invitations.create'));
        $response->assertSessionHas('invitation_notice.type', 'success');
        $invitation = Invitation::firstOrFail();
        $this->assertSame('invitee@example.com', $invitation->email);
        $this->assertSame(42, $invitation->inviter_id);
        Http::assertSent(function ($request) use ($invitation) {
            $data = $request->data();

            return $request->method() === 'GET'
                && str_starts_with($request->url(), 'https://gateway.example/api/email/emitter')
                && $data['abbr'] === 'test'
                && $data['email_from'] === 'sender@example.com'
                && $data['email_to'] === 'invitee@example.com'
                && str_contains($data['content'], $invitation->share_url);
        });
        Http::assertSentCount(1);
    }

    public function test_invitation_without_email_is_not_sent(): void
    {
        Http::fake();

        $response = $this->post(route('member.invitations.store'), ['email' => '']);

        $response->assertSessionHas('invitation_notice.type', 'success');
        $this->assertNull(Invitation::firstOrFail()->email);
        Http::assertNothingSent();
    }

    public function test_failed_delivery_keeps_invitation_and_warns_user(): void
    {
        Http::fake(['gateway.example/*' => Http::response('', 500)]);

        $response = $this->post(route('member.invitations.store'), ['email' => 'invitee@example.com']);

        $response->assertSessionHas('invitation_notice.type', 'warning');
        $this->assertSame('invitee@example.com', Invitation::firstOrFail()->email);
        Http::assertSentCount(1);
    }

    public function test_missing_gateway_configuration_warns_without_sending(): void
    {
        config(['services.genie_email.abbr' => null]);
        Http::fake();

        $response = $this->post(route('member.invitations.store'), ['email' => 'invitee@example.com']);

        $response->assertSessionHas('invitation_notice.type', 'warning');
        $this->assertSame('invitee@example.com', Invitation::firstOrFail()->email);
        Http::assertNothingSent();
    }

    public function test_connection_failure_keeps_invitation_and_warns_user(): void
    {
        Http::fake(function () {
            throw new ConnectionException('Connection failed');
        });

        $response = $this->post(route('member.invitations.store'), ['email' => 'invitee@example.com']);

        $response->assertSessionHas('invitation_notice.type', 'warning');
        $this->assertSame('invitee@example.com', Invitation::firstOrFail()->email);
    }

    public function test_invalid_email_does_not_create_or_send_invitation(): void
    {
        Http::fake();

        $response = $this->post(route('member.invitations.store'), ['email' => 'invalid']);

        $response->assertSessionHasErrors('email');
        $this->assertSame(0, Invitation::count());
        Http::assertNothingSent();
    }
}
