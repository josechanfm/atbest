<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\User;
use App\Models\Member;
use App\Models\Organization;
use App\Services\SmsService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class InvitationRegisterController extends Controller
{
    public function __construct(
        protected SmsService $smsService
    ) {}

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
        $memeber=Member::where('organization_id', 1)->where('user_id', $invitation->used_by)->first();
        
        if($memeber) {
            auth()->login($memeber->user);
            return redirect()->route('member.dashboard');
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
     * Step 1: Validate the invitation form, store pending data in session,
     * and send a 6-digit SMS verification code (only when the mobile is unique).
     */
    public function sendCode(Request $request, string $token): RedirectResponse
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if (! $invitation->isValid()) {
            return redirect()->route('invitations.show', $token);
        }

        $rules = [
            'name'     => ['required', 'string', 'max:255'],
            'mobile'   => ['required', 'string', 'max:32', 'regex:/^\+8536\d{7}$/', 'unique:users,mobile'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'language' => ['nullable', 'string', 'max:8'],
        ];

        // If the invitation is locked to a specific email, enforce it
        if ($invitation->email) {
            $rules['email'][] = 'in:' . $invitation->email;
        }

        $validated = $request->validate($rules);

        $otp = $this->smsService->sendVerificationCode($validated['mobile']);

        session()->put('invitation.pending', [
            'token'             => $token,
            'name'              => $validated['name'],
            'mobile'            => $validated['mobile'],
            'email'             => $validated['email'],
            'password_hash'     => Hash::make($validated['password']),
            'language'          => $validated['language'] ?? app()->getLocale(),
            'otp_hash'          => $otp['otp_hash'],
            'otp_expires_at'    => $otp['otp_expires_at'],
            'otp_attempts'      => 0,
            'mobile_verified'   => false,
            'mobile_validated_at' => null,
        ]);

        session()->flash('sms_status', 'code_sent');

        return back();
    }

    /**
     * Step 2: Verify the 6-digit SMS code against the session record.
     */
    public function verifyCode(Request $request, string $token): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);
        $pending = session('invitation.pending');

        if (! $pending || $pending['token'] !== $token) {
            throw ValidationException::withMessages([
                'code' => ['註冊資料已過期，請重新填寫並發送驗證碼。'],
            ]);
        }

        $result = $this->smsService->verifyCode($pending, $request->code);
        session()->put('invitation.pending', $result['pending']);

        if (! $result['verified']) {
            throw ValidationException::withMessages([
                'code' => [$result['error']],
            ]);
        }

        return back();
    }

    /**
     * Step 3: Convert verified session data into a User & Organization Member.
     */
    public function register(Request $request, string $token): RedirectResponse
    {
        $invitation = Invitation::where('token', $token)->firstOrFail();

        if (! $invitation->isValid()) {
            return redirect()->route('invitations.show', $token);
        }

        $pending = session('invitation.pending');

        if (! $pending || $pending['token'] !== $token || ! ($pending['mobile_verified'] ?? false)) {
            throw ValidationException::withMessages([
                'mobile' => ['請先完成手機簡訊驗證後再提交註冊。'],
            ]);
        }

        if (User::where('mobile', $pending['mobile'])->exists()) {
            throw ValidationException::withMessages([
                'mobile' => ['此手機號碼已被註冊。'],
            ]);
        }

        if (User::where('email', $pending['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => ['此電子郵件已被註冊。'],
            ]);
        }

        $user = DB::transaction(function () use ($pending, $invitation) {
            $user = User::create([
                'name'                => $pending['name'],
                'mobile'              => $pending['mobile'],
                'mobile_validated_at' => $pending['mobile_validated_at'] ?? now(),
                'email'               => $pending['email'],
                'email_verified_at'   => now(),
                'password'            => $pending['password_hash'],
                'language'            => $pending['language'] ?? app()->getLocale(),
                'is_active'           => true,
                'registration_method' => 'invitation_' . $invitation->id,
            ]);

            // Consume the invitation — single-use enforcement
            $invitation->markAsUsed($user);

            return $user;
        });

        $organization = Organization::find(1);
        Member::createOrganizationMember($organization, $user);

        session()->forget('invitation.pending');

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('member.dashboard');
    }
}