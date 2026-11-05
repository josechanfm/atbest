<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
        ]);
    }

    /**
     * Create a new invitation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['nullable', 'email', 'max:255'],
        ]);

        $invitation = Invitation::create([
            'token'      => Invitation::generateToken(),
            'inviter_id' => $request->user()->id,
            'email'      => $validated['email'] ?? null,
            'expires_at' => now()->addDays(3),
        ]);

        return back()->with('success', '邀請已建立');
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