<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\EmailGateway;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use RuntimeException;
use Illuminate\Support\Facades\Http;

class InvitationController extends Controller
{
    /**
     * List the current user's invitations.
     */
    public function create(Request $request)
    {
        $invitations = Invitation::with('usedBy:id,name,email')
            ->where('inviter_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(fn ($inv) => [
                'id'         => $inv->id,
                'token'      => $inv->token,
                'share_url'  => $inv->share_url,
                'email'      => $inv->email,
                'expires_at' => $inv->expires_at->toIso8601String(),
                'used_at'    => $inv->used_at?->toIso8601String(),
                'used_by'    => $inv->usedBy ? [
                    'id'    => $inv->usedBy->id,
                    'name'  => $inv->usedBy->name,
                    'email' => $inv->usedBy->email,
                ] : null,
                'is_valid'   => $inv->isValid(),
                'is_used'    => $inv->isUsed(),
                'is_expired' => $inv->isExpired(),
            ]);

        return Inertia::render('Member/InvitationCreate', [
            'invitations' => $invitations,
            'invitationNotice' => $request->session()->get('invitation_notice'),
        ]);
    }

    /**
     * Create a new invitation.
     */
    public function store(Request $request, EmailGateway $emailGateway)
    {
        $validated = $request->validate([
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $invitation = Invitation::create([
            'token'      => Invitation::generateToken(),
            'inviter_id' => $request->user()->id,
            'email'      => $validated['email'] ?? null,
            'expires_at' => now()->addDays(3),
            'used_by'   => auth()->id(),
        ]);

        if ($invitation->email) {
            try {
                $sent = $emailGateway->send(
                    $invitation->email,
                    '邀請您註冊',
                    "您好，您已獲邀註冊。請點擊以下連結接受邀請：\n"
                        . $invitation->share_url
                        . "\n此邀請有效期為三天，僅可使用一次。"
                );
            } catch (ConnectionException | RuntimeException $exception) {
                Log::warning('Invitation email delivery failed', [
                    'invitation_id' => $invitation->id,
                    'error_type' => get_class($exception),
                ]);
                $sent = false;
            }

            if (! $sent) {
                return back()->with('invitation_notice', [
                    'type' => 'warning',
                    'message' => '邀請已建立，但電郵發送失敗。請從下方列表複製連結並手動分享。',
                ]);
            }
            $invitation->markAsSent();
            return back()->with('invitation_notice', [
                'type' => 'success',
                'message' => '邀請已建立並已提交電郵發送。',
            ]);
        }

        return back()->with('invitation_notice', [
            'type' => 'success',
            'message' => '邀請已建立。請從下方列表複製連結並分享。',
        ]);
    }

    /**
     * Revoke (delete) an unused invitation.
     */
    public function destroy(Request $request, Invitation $invitation)
    {
        abort_if($invitation->inviter_id !== $request->user()->id, 403);
        abort_if($invitation->isUsed(), 422, 'Cannot revoke a used invitation.');

        $invitation->delete();

        return back()->with('success', '邀請已撤銷');
    }
}