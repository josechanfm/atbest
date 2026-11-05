<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class InvitationRegisterController extends Controller
{
    /**
     * Show the invitation landing page.
     */
    public function show(string $token)
    {
        $invitation = Invitation::with('inviter:id,name')
            ->where('token', $token)
            ->firstOrFail();

        if (! $invitation->isValid()) {
            return Inertia::render('Auth/InvitationInvalid', [
                'reason' => $invitation->isUsed() ? 'used' : 'expired',
            ]);
        }

        return Inertia::render('Auth/InvitationRegister', [
            'token'      => $invitation->token,
            'inviter'    => [
                'name' => $invitation->inviter->name,
            ],
            'email'      => $invitation->email, // pre-filled if locked
            'expires_at' => $invitation->expires_at->toIso8601String(),
        ]);
    }

    /**
     * Handle registration via invitation.
     */
    public function store(Request $request, string $token)
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if (! $invitation->isValid()) {
            return redirect()->route('invitations.show', $token);
        }

        $rules = [
            'name'     => ['required', 'string', 'max:255'],
            'mobile'   => ['required', 'string', 'max:32', 'unique:users,mobile'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'language' => ['nullable', 'string', 'max:8'],
        ];

        // If the invitation is locked to a specific email, enforce it
        if ($invitation->email) {
            $rules['email'][] = 'in:' . $invitation->email;
        }

        $validated = $request->validate($rules);

        $user = DB::transaction(function () use ($validated, $invitation) {
            $user = User::create([
                'name'                 => $validated['name'],
                'mobile'               => $validated['mobile'],
                'mobile_validated_at'  => null, // set after SMS verification
                'email'                => $validated['email'],
                'password'             => Hash::make($validated['password']),
                'language'             => $validated['language'] ?? app()->getLocale(),
                'is_active'            => true,
            ]);

            // Consume the invitation — single-use enforcement
            $invitation->markAsUsed($user);

            return $user;
        });

        event(new Registered($user));

        //Auth::login($user);

        return redirect()->route('/');
    }
}