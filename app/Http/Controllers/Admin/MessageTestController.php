<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EmailGateway;
use App\Services\SmsService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use RuntimeException;

class MessageTestController extends Controller
{
    /**
     * Show the email / SMS gateway test page.
     */
    public function index()
    {
        return Inertia::render('Admin/MessageTest', [
            'emailConfigured' => $this->emailConfigured(),
            'smsConfigured'   => $this->smsConfigured(),
        ]);
    }

    /**
     * Check the health of both gateways (no message is sent).
     */
    public function health()
    {
        return response()->json([
            'email' => $this->checkEmailHealth(),
            'sms'   => $this->checkSmsHealth(),
        ]);
    }

    /**
     * Send a test email through the gateway.
     */
    public function sendTestEmail(Request $request, EmailGateway $emailGateway)
    {
        $validated = $request->validate([
            'to'      => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $startedAt = microtime(true);

        try {
            $sent = $emailGateway->send(
                $validated['to'],
                $validated['subject'],
                $validated['content']
            );
        } catch (ConnectionException $exception) {
            return $this->sendResult(false, 'Connection failed: ' . $exception->getMessage(), $startedAt);
        } catch (RequestException $exception) {
            $response = $exception->response;

            return $this->sendResult(false, sprintf(
                'HTTP %d: %s',
                $response->status(),
                mb_substr($response->body(), 0, 500)
            ), $startedAt);
        } catch (RuntimeException $exception) {
            return $this->sendResult(false, $exception->getMessage(), $startedAt);
        }

        return $this->sendResult($sent, $sent ? null : 'Gateway reported a failure (see server logs).', $startedAt);
    }

    /**
     * Send a test SMS through the gateway.
     */
    public function sendTestSms(Request $request, SmsService $smsService)
    {
        $validated = $request->validate([
            'mobile'  => ['required', 'string', 'max:20'],
            'content' => ['required', 'string', 'max:500'],
        ]);

        $startedAt = microtime(true);
        $result = $smsService->send($validated['mobile'], $validated['content']);

        return $this->sendResult(
            $result['success'],
            $result['success'] ? null : ($result['error'] ?? 'Unknown error'),
            $startedAt,
            $result['response']
        );
    }

    /**
     * Whether the email gateway configuration is complete.
     */
    private function emailConfigured(): bool
    {
        return config('services.genie_email.base_url') !== ''
            && ! blank(config('services.genie_email.abbr'))
            && ! blank(config('services.genie_email.from'));
    }

    /**
     * Whether the SMS gateway configuration is complete.
     */
    private function smsConfigured(): bool
    {
        return config('services.genie_sms.base_url') !== ''
            && ! blank(config('services.genie_sms.abbr'));
    }

    /**
     * Check the email gateway health without sending a message.
     *
     * Verifies the configuration is complete and the gateway host is reachable.
     * It deliberately does NOT call the emitter endpoint, so no email is sent.
     */
    private function checkEmailHealth(): array
    {
        if (! $this->emailConfigured()) {
            return [
                'ok'      => false,
                'message' => 'Email gateway configuration is incomplete.',
            ];
        }

        return $this->pingHost(config('services.genie_email.base_url'));
    }

    /**
     * Check the SMS gateway health without sending a message.
     *
     * Verifies the configuration is complete and the gateway host is reachable.
     * It deliberately does NOT call the emitter endpoint, so no SMS is sent.
     */
    private function checkSmsHealth(): array
    {
        if (! $this->smsConfigured()) {
            return [
                'ok'      => false,
                'message' => 'SMS gateway configuration is incomplete.',
            ];
        }

        return $this->pingHost(config('services.genie_sms.base_url'));
    }

    /**
     * Verify a gateway host is reachable by requesting its root URL.
     *
     * Any HTTP response (even 404) proves the server is up and reachable.
     */
    private function pingHost(?string $baseUrl): array
    {
        $baseUrl = rtrim((string) $baseUrl, '/');

        if ($baseUrl === '') {
            return [
                'ok'      => false,
                'message' => 'Gateway base URL is not configured.',
            ];
        }

        $parts = parse_url($baseUrl);
        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'] ?? '';

        if ($host === '') {
            return [
                'ok'      => false,
                'message' => 'Could not determine the gateway host from the base URL.',
            ];
        }

        $rootUrl = $scheme . '://' . $host;
        $startedAt = microtime(true);

        try {
            $response = Http::timeout(10)
                ->withoutVerifying()
                ->acceptJson()
                ->get($rootUrl);
        } catch (ConnectionException $exception) {
            return [
                'ok'      => false,
                'message' => 'Connection failed: ' . $exception->getMessage(),
            ];
        }

        $elapsedMs = (int) round((microtime(true) - $startedAt) * 1000);

        return [
            'ok'        => true,
            'status'    => $response->status(),
            'elapsedMs' => $elapsedMs,
            'message'   => "Gateway host {$host} responded with HTTP {$response->status()} in {$elapsedMs}ms.",
        ];
    }

    /**
     * Build a JSON result for a test send.
     */
    private function sendResult(bool $success, ?string $error, float $startedAt, mixed $response = null): \Illuminate\Http\JsonResponse
    {
        $elapsedMs = (int) round((microtime(true) - $startedAt) * 1000);

        if (! $success) {
            Log::warning('Gateway test send failed', ['error' => $error]);
        }

        return response()->json([
            'success'   => $success,
            'error'     => $error,
            'response'  => $response,
            'elapsedMs' => $elapsedMs,
        ]);
    }
}
