<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Organization;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\SmsService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RegisterUserController extends Controller
{
    public function __construct(
        protected SmsService $smsService
    ) {}

    public function create(): Response
    {
        return Inertia::render('Auth/Register', [
            'pendingRegistration' => session('registration.pending'),
        ]);
    }

    /**
     * Step 1: Validate registration fields, store pending data in session, and send 6-digit SMS code.
     */
    public function preRegister(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'given_name' => 'nullable|string|max:255',
            'family_name' => 'nullable|string|max:255',
            'mobile' => ['required', 'string', 'max:20', 'regex:/^\+8536\d{7}$/', 'unique:users,mobile'],
            'email' => 'required|string|lowercase|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'registration_code' => 'required|exists:organizations,registration_code',
        ]);

        $otp = $this->smsService->sendVerificationCode($validated['mobile']);

        // Store pre-registration payload & OTP state in session
        session()->put('registration.pending', [
            'name' => $validated['name'],
            'given_name' => $validated['given_name'] ?? null,
            'family_name' => $validated['family_name'] ?? null,
            'mobile' => $validated['mobile'],
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'registration_code' => $validated['registration_code'],
            'otp_hash' => $otp['otp_hash'],
            'otp_expires_at' => $otp['otp_expires_at'],
            'otp_attempts' => 0,
            'mobile_verified' => false,
            'mobile_validated_at' => null,
        ]);

        session()->flash('sms_status', 'code_sent');

        return back();
    }

    /**
     * Step 2: Verify the 6-digit SMS code against the session record.
     */
    public function verifyCode(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);

        $pending = session('registration.pending');

        if (! $pending) {
            throw ValidationException::withMessages([
                'code' => ['註冊資料已過期，請重新填寫並發送驗證碼。'],
            ]);
        }

        $result = $this->smsService->verifyCode($pending, $request->code);
        session()->put('registration.pending', $result['pending']);

        if (! $result['verified']) {
            throw ValidationException::withMessages([
                'code' => [$result['error']],
            ]);
        }

        Cache::forget('registration_' . $pending['mobile']);

        return back();
    }

    /**
     * Step 3: Convert verified session data into a User & Organization Member.
     */
    public function store(Request $request): RedirectResponse
    {
        $pending = session('registration.pending');

        if ($request->filled('verification_code') && ! ($pending['mobile_verified'] ?? false)) {
            $request->merge(['code' => $request->input('verification_code')]);
            $this->verifyCode($request);
            $pending = session('registration.pending');
        }

        if (! $pending || ! ($pending['mobile_verified'] ?? false)) {
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

        $organization = Organization::where('registration_code', $pending['registration_code'])->firstOrFail();

        $user = User::create([
            'name' => $pending['name'],
            'given_name' => $pending['given_name'],
            'family_name' => $pending['family_name'],
            'mobile' => $pending['mobile'],
            'mobile_validated_at' => $pending['mobile_validated_at'] ?? now(),
            'email' => $pending['email'],
            'email_verified_at' => null,
            'password' => $pending['password_hash'],
            'registration_method' => 'self_registration_' . $pending['registration_code'],
        ]);

        Member::createOrganizationMember($organization, $user);

        session()->forget('registration.pending');

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}