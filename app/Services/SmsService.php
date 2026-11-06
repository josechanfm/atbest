<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
            $response = Http::timeout(30)->acceptJson()->get($baseUrl . '/api/sms/emitter', [
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
}
