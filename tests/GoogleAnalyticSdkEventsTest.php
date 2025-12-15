<?php

namespace FunnyDev\GoogleAnalytic\Tests;

use FunnyDev\GoogleAnalytic\GoogleAnalyticSdk;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class GoogleAnalyticSdkEventsTest extends TestCase
{
    public function test_event_add_to_cart_sends_expected_payload_and_uses_cookie_client_id(): void
    {
        Http::fake(fn () => Http::response('', 204));

        $request = Request::create('/', 'GET');
        $request->cookies->set('_ga', 'GA1.1.123.456');
        $this->app->instance('request', $request);
        Auth::shouldReceive('check')->andReturn(false);

        $ok = GoogleAnalyticSdk::eventAddToCart(
            items: [['item_id' => 'sku-1', 'item_name' => 'Demo']],
            value: 12.5,
            currency: 'USD',
            extraParams: ['coupon' => 'WELCOME'],
            debug: true
        );

        $this->assertTrue($ok);

        Http::assertSent(function (ClientRequest $request) {
            $data = $request->data();
            $this->assertSame('add_to_cart', $data['events'][0]['name'] ?? null);
            $this->assertSame('123.456', $data['client_id'] ?? null);
            $this->assertArrayNotHasKey('user_id', $data);

            $params = $data['events'][0]['params'] ?? [];
            $this->assertSame('USD', $params['currency'] ?? null);
            $this->assertSame(12.5, $params['value'] ?? null);
            $this->assertSame('WELCOME', $params['coupon'] ?? null);
            $this->assertSame(true, $params['debug_mode'] ?? null);
            $this->assertSame('sku-1', $params['items'][0]['item_id'] ?? null);
            return true;
        });
    }
}
