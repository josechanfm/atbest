<?php

namespace Tests\Feature;

use App\Services\SmsService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmsServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.genie_sms.base_url' => 'https://gateway.example/',
            'services.genie_sms.abbr' => 'test-sms',
        ]);
    }

    public function test_it_sends_a_local_macau_number_to_the_genie_gateway(): void
    {
        Http::fake(['gateway.example/*' => Http::response(['accepted' => true])]);

        $result = app(SmsService::class)->send('66666666', 'Hello & welcome');

        $this->assertSame(['success' => true, 'response' => ['accepted' => true], 'error' => null], $result);
        Http::assertSent(function ($request) {
            return $request->method() === 'GET'
                && str_starts_with($request->url(), 'https://gateway.example/api/sms/emitter?')
                && $request->data() === [
                    'abbr' => 'test-sms',
                    'mobile' => '85366666666',
                    'content' => 'Hello & welcome',
                ];
        });
        Http::assertSentCount(1);
    }

    public function test_it_normalizes_international_numbers_without_adding_a_second_prefix(): void
    {
        Http::fake(['gateway.example/*' => Http::response('OK')]);

        $result = app(SmsService::class)->send('+853 6666-6666', 'Hello');

        $this->assertTrue($result['success']);
        $this->assertSame('OK', $result['response']);
        Http::assertSent(fn ($request) => $request->data()['mobile'] === '85366666666');
    }

    public function test_it_reports_http_failures(): void
    {
        Http::fake(['gateway.example/*' => Http::response('Failed', 500)]);

        $result = app(SmsService::class)->send('85366666666', 'Hello');

        $this->assertSame(['success' => false, 'response' => 'Failed', 'error' => 'HTTP status 500'], $result);
        Http::assertSentCount(1);
    }

    public function test_it_reports_connection_failures(): void
    {
        Http::fake(function () {
            throw new ConnectionException('Connection failed');
        });

        $result = app(SmsService::class)->send('85366666666', 'Hello');

        $this->assertSame(['success' => false, 'response' => null, 'error' => 'SMS gateway connection failed.'], $result);
    }

    public function test_it_reports_missing_configuration_without_sending(): void
    {
        config(['services.genie_sms.abbr' => null]);
        Http::fake();

        $result = app(SmsService::class)->send('85366666666', 'Hello');

        $this->assertFalse($result['success']);
        $this->assertSame('SMS gateway configuration is incomplete.', $result['error']);
        Http::assertNothingSent();
    }

    public function test_it_rejects_invalid_mobile_numbers(): void
    {
        Http::fake();

        foreach (['', '+ -', 'abc66666666'] as $mobile) {
            $result = app(SmsService::class)->send($mobile, 'Hello');
            $this->assertFalse($result['success']);
            $this->assertSame('Invalid mobile number.', $result['error']);
        }
        Http::assertNothingSent();
    }
}
