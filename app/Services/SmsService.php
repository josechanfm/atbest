<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class SmsService
{
    /**
     * @return array{success: bool, response: mixed, error: string|null}
     */
    public function send(string $mobile, string $content): array
    {
        $baseUrl = rtrim((string) config('services.genie_sms.base_url'), '/');
        $abbr = config('services.genie_sms.abbr');

        if ($baseUrl === '' || blank($abbr)) {
            Log::error('SMS gateway configuration is incomplete.');

            return ['success' => false, 'response' => null, 'error' => 'SMS gateway configuration is incomplete.'];
        }

        $mobile = trim($mobile);
        if (! preg_match('/^[0-9+\-() ]+$/', $mobile)) {
            Log::warning('SMS send failed: invalid mobile number.');

            return ['success' => false, 'response' => null, 'error' => 'Invalid mobile number.'];
        }

        $mobile = preg_replace('/\D/', '', $mobile);
        if (preg_match('/^6\d{7}$/', $mobile)) {
            $mobile = '853' . $mobile;
        }
        if ($mobile === '') {
            Log::warning('SMS send failed: invalid mobile number.');

            return ['success' => false, 'response' => null, 'error' => 'Invalid mobile number.'];
        }

        try {
            $response = Http::timeout(30)
            ->withoutVerifying() // <-- Use this instead
            ->acceptJson()
            ->get($baseUrl, [
                'abbr' => $abbr,
                'mobile' => $mobile,
                'content' => $content,
            ]);
        } catch (ConnectionException $exception) {
            Log::error('SMS gateway connection failed.');

            return ['success' => false, 'response' => null, 'error' => 'SMS gateway connection failed.'];
        }

        if (! $response->successful()) {
            Log::error('SMS gateway request failed.', ['status' => $response->status()]);

            return [
                'success' => false,
                'response' => $response->body(),
                'error' => "HTTP status {$response->status()}",
            ];
        }

        return [
            'success' => true,
            'response' => $response->json() ?? $response->body(),
            'error' => null,
        ];
    }

    /**
     * Generate a 6-digit verification code and send it via SMS.
     *
     * @return array{code: string, otp_hash: string, otp_expires_at: int}
     */
    public function sendVerificationCode(string $mobile, int $ttlMinutes = 10): array
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        app()->setLocale('zh_TW');
        $content = trans('sms.verification_code', ['code' => $code]);
        $result = $this->send($mobile, $content);

        if (! ($result['success'] ?? false)) {
            throw ValidationException::withMessages([
                'mobile' => [trans('accounts_and_security.sms_send_failed')],
            ]);
        }

        return [
            'code' => $code,
            'otp_hash' => Hash::make($code),
            'otp_expires_at' => now()->addMinutes($ttlMinutes)->timestamp,
        ];
    }

    /**
     * Verify a 6-digit code against a stored OTP record.
     *
     * Expects $pending to carry: otp_hash, otp_expires_at, otp_attempts.
     * Returns ['verified' => bool, 'pending' => array, 'error' => string|null].
     * The caller should persist $result['pending'] back to the session and
     * surface $result['error'] when $result['verified'] is false.
     *
     * @param  array  $pending
     * @return array{verified: bool, pending: array, error: string|null}
     */
    public function verifyCode(array $pending, string $code): array
    {
        if (now()->timestamp > ($pending['otp_expires_at'] ?? 0)) {
            return ['verified' => false, 'pending' => $pending, 'error' => '驗證碼已過期，請重新發送。'];
        }

        if (($pending['otp_attempts'] ?? 0) >= 5) {
            return ['verified' => false, 'pending' => $pending, 'error' => '驗證嘗試次數過多，請重新發送驗證碼。'];
        }

        if (! Hash::check($code, $pending['otp_hash'])) {
            $pending['otp_attempts'] = ($pending['otp_attempts'] ?? 0) + 1;

            return ['verified' => false, 'pending' => $pending, 'error' => '驗證碼錯誤，請重新輸入。'];
        }

        $pending['mobile_verified'] = true;
        $pending['mobile_validated_at'] = now()->toDateTimeString();

        return ['verified' => true, 'pending' => $pending, 'error' => null];
    }
}
