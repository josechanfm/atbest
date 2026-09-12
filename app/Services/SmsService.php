<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $baseUrl;
    protected int $clientId;

    public function __construct()
    {
        // Read directly from .env
        $this->baseUrl = env('SMS_BASE_URL', 'https://genie.hubis.ltd/api/sms/emitter');
        $this->clientId = (int) env('SMS_CLIENT_ID', 2);
    }

    /**
     * Send an SMS message.
     *
     * @param string $mobile   Recipient phone number (without country code? adjust as needed)
     * @param string $content  Message content (URL-encoded)
     * @param int|null $clientId Optional override of the default client ID
     * @return array ['success' => bool, 'response' => mixed, 'error' => string|null]
     */
    public function send(string $mobile, string $content, ?int $clientId = null): array
    {
        $clientId = $clientId ?? $this->clientId;

        // Build the full URL with query parameters
        $url = $this->baseUrl . '?' . http_build_query([
            'client_id' => $clientId,
            'mobile'    => $mobile,
            'content'   => $content,
        ]);
        dd($url, $content, $mobile, $clientId);
        
        try {
            $response = Http::timeout(30)->get($url);

            if ($response->successful()) {
                // Log success
                Log::info('SMS sent successfully', [
                    'mobile'    => $mobile,
                    'client_id' => $clientId,
                    'response'  => $response->body(),
                ]);

                return [
                    'success'  => true,
                    'response' => $response->json() ?? $response->body(),
                    'error'    => null,
                ];
            }

            // Log failure
            Log::error('SMS sending failed', [
                'mobile'    => $mobile,
                'client_id' => $clientId,
                'status'    => $response->status(),
                'body'      => $response->body(),
            ]);

            return [
                'success'  => false,
                'response' => $response->body(),
                'error'    => "HTTP status {$response->status()}",
            ];
        } catch (\Exception $e) {
            Log::error('SMS exception', [
                'mobile' => $mobile,
                'error'  => $e->getMessage(),
            ]);

            return [
                'success'  => false,
                'response' => null,
                'error'    => $e->getMessage(),
            ];
        }
    }
}