<?php

namespace FunnyDev\GoogleAnalytic\Tests;

use FunnyDev\GoogleAnalytic\GoogleAnalyticSdk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoogleAnalyticSdkResolveIdsTest extends TestCase
{
    public function test_resolve_ids_parses_ga_cookie_and_auth_uuid(): void
    {
        $request = Request::create('/', 'GET');
        $request->cookies->set('_ga', 'GA1.1.1234567890.9876543210');
        $this->app->instance('request', $request);

        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn((object) ['uuid' => 'uuid-1']);

        [$clientId, $userId] = GoogleAnalyticSdk::resolveIdsFromRequest();

        $this->assertSame('1234567890.9876543210', $clientId);
        $this->assertSame('uuid-1', $userId);
    }

    public function test_resolve_ids_returns_empty_client_id_when_cookie_invalid(): void
    {
        $request = Request::create('/', 'GET');
        $request->cookies->set('_ga', 'invalid');
        $this->app->instance('request', $request);

        Auth::shouldReceive('check')->andReturn(false);

        [$clientId, $userId] = GoogleAnalyticSdk::resolveIdsFromRequest();

        $this->assertSame('', $clientId);
        $this->assertNull($userId);
    }
}
