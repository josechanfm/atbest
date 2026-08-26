<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $url;
    protected string $username;
    protected string $password;
    protected string $from;
    protected int $timeout;
    protected int $retry;

    public function __construct()
    {
        $this->url      = config('sms.gateway.url');
        $this->username = config('sms.gateway.username');
        $this->password = config('sms.gateway.password');
        $this->from     = config('sms.gateway.from');
        $this->timeout  = config('sms.timeout', 5);
        $this->retry    = config('sms.retry', 1);
    }

    /**
     * Send an SMS
     *
     * @param string $to      Recipient phone number
     * @param string $message Message text
     * @param string|null $from Optional sender override
     * @return bool
     * @throws \Exception
     */
    public function send(string $to, string $message, ?string $from = null): bool
    {
        $from = $from ?? $this->from;

        // Build query parameters (URL-encode the message)
        $params = [
            'username' => $this->username,
            'password' => $this->password,
            'from'     => $from,
            'to'       => $to,
            'text'     => $message, // Will be encoded automatically by Laravel HTTP client
        ];

        // Send with retry logic
        $attempts = 0;
        while ($attempts < $this->retry) {
            try {
                $response = Http::timeout($this->timeout)
                    ->get($this->url, $params);

                // Check response status and content (adjust to your gateway's response)
                if ($response->successful()) {
                    // Your gateway may return a specific success code, e.g., "0" or "OK"
                    $body = $response->body();
                    if ($this->isSuccessfulResponse($body)) {
                        return true;
                    }
                    Log::warning('SMS gateway returned error', ['response' => $body]);
                } else {
                    Log::warning('SMS HTTP error', ['status' => $response->status()]);
                }
            } catch (\Exception $e) {
                Log::error('SMS sending exception', ['message' => $e->getMessage()]);
            }
            $attempts++;
        }

        throw new \Exception('Failed to send SMS after ' . $this->retry . ' attempts.');
    }

    /**
     * Determine if the gateway response indicates success.
     * Adjust this based on your provider's documentation.
     */
    protected function isSuccessfulResponse(string $response): bool
    {
        // Example: if the gateway returns "0" or "OK" or "Success"
        // You need to inspect the actual response from your gateway.
        // For now, assume a 200 OK with any content is success.
        return true;
    }
}