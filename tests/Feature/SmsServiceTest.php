<?php

namespace Tests\Feature;

use App\Services\SmsService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmsServiceTest extends TestCase
{
    protected SmsService $smsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->smsService = new SmsService();
    }

    /** @test */
    public function it_sends_an_sms_successfully()
    {
        // 1. Fake the external HTTP call
        Http::fake([
            'emg.ctm-mobile.com:*' => Http::response('0', 200), // assume '0' means success
        ]);

        // 2. Execute the service
        $result = $this->smsService->send('1234567890', 'Test message');

        // 3. Assert
        $this->assertTrue($result);

        // 4. Verify that the HTTP request was sent with the correct parameters
        Http::assertSent(function ($request) {
            return $request->url() === config('sms.gateway.url') &&
                   $request->data()['username'] === config('sms.gateway.username') &&
                   $request->data()['password'] === config('sms.gateway.password') &&
                   $request->data()['to'] === '1234567890' &&
                   $request->data()['text'] === 'Test message';
        });
    }

    /** @test */
    public function it_throws_exception_on_http_failure()
    {
        // Fake a server error (500)
        Http::fake([
            'emg.ctm-mobile.com:*' => Http::response('Error', 500),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to send SMS');

        $this->smsService->send('1234567890', 'Test message');
    }

    /** @test */
    public function it_retries_on_failure()
    {
        // Configure retries to 3 (you can set in config for this test)
        config(['sms.retry' => 3]);

        // Fake the first two calls as errors, third as success
        Http::fakeSequence()
            ->push('Error', 500)
            ->push('Error', 500)
            ->push('0', 200);

        $result = $this->smsService->send('1234567890', 'Test message');

        $this->assertTrue($result);
        // Assert that 3 requests were made
        Http::assertSentCount(3);
    }

    /** @test */
    public function it_handles_custom_from_number()
    {
        Http::fake([
            'emg.ctm-mobile.com:*' => Http::response('0', 200),
        ]);

        $result = $this->smsService->send('1234567890', 'Test', 'CUSTOM');

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request->data()['from'] === 'CUSTOM';
        });
    }
}