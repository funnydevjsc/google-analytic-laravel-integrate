<?php

namespace App\Console\Commands;

use FunnyDev\GoogleAnalytic\GoogleAnalyticSdk;
use Illuminate\Console\Command;

class GoogleAnalyticCommand extends Command
{
    protected $signature = 'google-analytic:crawl';

    protected $description = 'Crawl analytics data from Google Analytics';

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $instance = new GoogleAnalyticSdk();
        print_r($instance->getReport(start_date: '2024-01-01', end_date: '2024-05-12'));
    }
}
