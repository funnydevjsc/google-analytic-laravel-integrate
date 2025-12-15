<?php

namespace FunnyDev\GoogleAnalytic\Tests;

use FunnyDev\GoogleAnalytic\GoogleAnalyticServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            GoogleAnalyticServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Prevent GoogleAnalyticServiceProvider::boot() from running vendor:publish automatically
        // by ensuring the target config file exists in the Testbench sandbox.
        $configFile = $app->configPath('google-analytic.php');
        @mkdir(dirname($configFile), 0777, true);
        if (!file_exists($configFile)) {
            file_put_contents($configFile, "<?php\n\nreturn [];\n");
        }

        $app['config']->set('google-analytic.measurement_id', 'G-TEST');
        $app['config']->set('google-analytic.measurement_api_secret', 'secret');
    }
}
