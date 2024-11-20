<?php

namespace FunnyDev\GoogleAnalytic;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\ServiceProvider;

class GoogleAnalyticServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot(Router $router): void
    {
        $this->publishes([
            __DIR__.'/../config/google-analytic.php' => config_path('google-analytic.php'),
            __DIR__.'/../config/google-service.php' => config_path('google-service.php'),
            __DIR__ . '/../resources/views/google-analytics' => resource_path('views/vendor/google-analytics'),
            __DIR__.'/../app/Console/Commands/GoogleAnalyticCommand.php' => app_path('Console/Commands/GoogleAnalyticCommand.php')
        ], 'google-analytic');

        try {
            if (!file_exists(config_path('google-analytic.php'))) {
                $this->commands([
                    \Illuminate\Foundation\Console\VendorPublishCommand::class,
                ]);

                Artisan::call('vendor:publish', ['--provider' => 'FunnyDev\\GoogleAnalytic\\GoogleAnalyticServiceProvider', '--tag' => ['google-analytic']]);
            }
        } catch (\Exception $e) {}
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/google-analytic.php', 'google-analytic'
        );
        $this->mergeConfigFrom(
            __DIR__.'/../config/google-service.php', 'google-service'
        );
        $this->app->singleton(\FunnyDev\GoogleAnalytic\GoogleAnalyticSdk::class, function () {
            return new \FunnyDev\GoogleAnalytic\GoogleAnalyticSdk;
        });
    }
}
