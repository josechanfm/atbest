<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class MobilePasswordController extends Controller
{
    // Show the reset page (optional)
    public function showResetForm()
    {
        return Inertia::render('Auth/MobilePasswordReset');
    }

    // Step 1: Send SMS with verification code
    public function sendResetCode(Request $request, SmsService $smsService)
    {
        
        $request->validate([
            'mobile' => 'required|string|regex:/^\+8536\d{7}$/',
        ]);

        // Check if user exists with this mobile
        $user = User::where('mobile', $request->mobile)->first();
        if (!$user) {
            throw ValidationException::withMessages([
                'mobile' => trans('mobile_not_found'),
            ]);
        }

        // Generate a 6-digit code
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put('password_reset_' . $request->mobile, $code, now()->addMinutes(10));
        app()->setLocale('zh_TW');
        // Send SMS
        $content = trans('sms.reset_code', ['code' => $code]);
        $result = $smsService->send($request->mobile, $content);
        if (!$result['success']) {
            throw ValidationException::withMessages([
                'mobile' => trans('sms_send_failed'),
            ]);
        }

        return back()->with('success', trans('code_sent'));
    }

    // Step 2: Verify the code
    public function verifyCode(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string|regex:/^\+8536\d{7}$/',
            'code'   => 'required|string|size:6',
        ]);

        $cachedCode = Cache::get('password_reset_' . $request->mobile);
        if (!$cachedCode || $cachedCode !== $request->code) {
            throw ValidationException::withMessages([
                'code' => trans('invalid_code'),
            ]);
        }

        // Mark as verified (store a temporary token or flag)
        Cache::put('password_reset_verified_' . $request->mobile, true, now()->addMinutes(15));

        return back()->with('success', trans('code_verified'));
    }

    // Step 3: Reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string|regex:/^\+8536\d{7}$/',
            'code'   => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Verify code again (and the verified flag)
        $cachedCode = Cache::get('password_reset_' . $request->mobile);
        $verified = Cache::get('password_reset_verified_' . $request->mobile);
        if (!$cachedCode || $cachedCode !== $request->code || !$verified) {
            throw ValidationException::withMessages([
                'code' => trans('invalid_code'),
            ]);
        }

        $user = User::where('mobile', $request->mobile)->first();
        if (!$user) {
            throw ValidationException::withMessages([
                'mobile' => trans('mobile_not_found'),
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Clear cache
        Cache::forget('password_reset_' . $request->mobile);
        Cache::forget('password_reset_verified_' . $request->mobile);

        return redirect()->route('login')->with('success', trans('password_reset_success'));
    }
}