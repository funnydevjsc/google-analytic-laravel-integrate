<?php

namespace FunnyDev\GoogleAnalytic;

use App\Http\Middleware\GoogleAnalyticMiddleware;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        $this->app->singleton(\FunnyDev\GoogleAnalytic\GoogleAnalyticSdk::class, function ($app) {
            $merchant = $app['config']['ninepay.merchant'];
            $secret = $app['config']['ninepay.secret'];
            $sum = $app['config']['ninepay.sum'];
            $server = $app['config']['ninepay.server'];
            return new \FunnyDev\GoogleAnalytic\GoogleAnalyticSdk($merchant, $secret, $sum, $server);
        });
    }
}
