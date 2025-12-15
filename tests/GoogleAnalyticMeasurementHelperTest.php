<?php

namespace FunnyDev\GoogleAnalytic\Tests;

use FunnyDev\GoogleAnalytic\GoogleAnalyticMeasurementHelper;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

class GoogleAnalyticMeasurementHelperTest extends TestCase
{
    public function test_send_sets_client_id_when_ga_client_id_format(): void
    {
        Http::fake(fn () => Http::response('', 204));

        $helper = new GoogleAnalyticMeasurementHelper('G-TEST', 'secret');
        $ok = $helper->send('1234567890.1234567890', 'custom', ['foo' => 'bar']);

        $this->assertTrue($ok);

        Http::assertSent(function (Request $request) {
            $this->assertSame('POST', $request->method());
            $this->assertSame(
                'https://www.google-analytics.com/mp/collect?measurement_id=G-TEST&api_secret=secret',
                $request->url()
            );

            $data = $request->data();
            $this->assertSame('1234567890.1234567890', $data['client_id'] ?? null);
            $this->assertArrayNotHasKey('user_id', $data);
            $this->assertSame('custom', $data['events'][0]['name'] ?? null);
            $this->assertSame(['foo' => 'bar'], $data['events'][0]['params'] ?? null);
            return true;
        });
    }

    public function test_send_falls_back_to_user_id_when_client_id_is_not_ga_format(): void
    {
        Http::fake(fn () => Http::response('', 204));

        $helper = new GoogleAnalyticMeasurementHelper('G-TEST', 'secret');
        $ok = $helper->send('uuid-123', 'custom', []);

        $this->assertTrue($ok);

        Http::assertSent(function (Request $request) {
            $data = $request->data();
            $this->assertArrayNotHasKey('client_id', $data);
            $this->assertSame('uuid-123', $data['user_id'] ?? null);
            return true;
        });
    }

    public function test_send_adds_debug_mode_to_event_params_when_enabled(): void
    {
        Http::fake(fn () => Http::response('', 204));

        $helper = new GoogleAnalyticMeasurementHelper('G-TEST', 'secret');
        $ok = $helper->send('123.456', 'custom', ['x' => 1], null, true);

        $this->assertTrue($ok);

        Http::assertSent(function (Request $request) {
            $data = $request->data();
            $this->assertSame(true, $data['events'][0]['params']['debug_mode'] ?? null);
            return true;
        });
    }

    public function test_send_adds_user_properties_when_provided(): void
    {
        Http::fake(fn () => Http::response('', 204));

        $helper = new GoogleAnalyticMeasurementHelper('G-TEST', 'secret');
        $ok = $helper->send(
            '123.456',
            'custom',
            ['x' => 1],
            'user-1',
            false,
            [
                'customer_type' => 'paid',
                'plan' => ['value' => 'pro'],
                'empty' => '',
            ]
        );

        $this->assertTrue($ok);

        Http::assertSent(function (Request $request) {
            $data = $request->data();
            $this->assertSame(['value' => 'paid'], $data['user_properties']['customer_type'] ?? null);
            $this->assertSame(['value' => 'pro'], $data['user_properties']['plan'] ?? null);
            $this->assertArrayNotHasKey('empty', $data['user_properties'] ?? []);
            return true;
        });
    }

    public function test_send_uses_debug_endpoint_when_enabled(): void
    {
        Http::fake(fn () => Http::response(['validationMessages' => []], 200));

        $helper = new GoogleAnalyticMeasurementHelper('G-TEST', 'secret');
        $ok = $helper->send('123.456', 'custom', ['x' => 1], null, true, [], null, true);

        $this->assertTrue($ok);

        Http::assertSent(function (Request $request) {
            $this->assertSame(
                'https://www.google-analytics.com/debug/mp/collect?measurement_id=G-TEST&api_secret=secret',
                $request->url()
            );
            $data = $request->data();
            $this->assertSame(true, $data['events'][0]['params']['debug_mode'] ?? null);
            return true;
        });
    }
}
