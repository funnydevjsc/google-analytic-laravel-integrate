<?php

namespace FunnyDev\GoogleAnalytic\Tests;

use FunnyDev\GoogleAnalytic\GoogleAnalyticSdk;
use FunnyDev\GoogleAnalytic\GoogleAnalyticServiceProvider;

class GoogleAnalyticServiceProviderTest extends TestCase
{
    public function test_registers_singleton_sdk(): void
    {
        $a = $this->app->make(GoogleAnalyticSdk::class);
        $b = $this->app->make(GoogleAnalyticSdk::class);

        $this->assertInstanceOf(GoogleAnalyticSdk::class, $a);
        $this->assertSame($a, $b);
    }

    public function test_config_is_merged(): void
    {
        $config = $this->app['config']->get('google-analytic');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('measurement_id', $config);
        $this->assertArrayHasKey('measurement_api_secret', $config);
    }

    public function test_publishes_paths_are_registered_for_tag(): void
    {
        $paths = GoogleAnalyticServiceProvider::pathsToPublish(GoogleAnalyticServiceProvider::class, 'google-analytic');

        $this->assertIsArray($paths);
        $this->assertNotEmpty($paths);
    }
}
