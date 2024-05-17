<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use FunnyDev\GoogleAnalytic\GoogleAnalyticSdk;
use Illuminate\Console\Command;

class GoogleAnalyticCommand extends Command
{
    protected $signature = 'google-analytic:crawl';

    protected $description = 'Crawl analytics data from Google Analytics';

    /**
     * @throws \Exception
     */
    private function fetchAnalyticDataAndSave(GoogleAnalyticSdk $analytics, string $timeValue, callable $dateModifier): array
    {
        $today = Carbon::now();
        $end_date = $today->format('Y-m-d');
        $start_date = $dateModifier($today)->format('Y-m-d');

        return $analytics->getReport(start_date: $start_date, end_date: $end_date);
    }

    /**
     * @throws \Exception
     */
    public function handle(): void
    {
        $analytics = new GoogleAnalyticSdk();
        $analyticsData_1d = $this->fetchAnalyticDataAndSave($analytics, '1d', fn($today) => $today->subDay());
        $analyticsData_7d = $this->fetchAnalyticDataAndSave($analytics, '7d', fn($today) => $today->subWeek());
        $analyticsData_28d = $this->fetchAnalyticDataAndSave($analytics, '28d', fn($today) => $today->subMonth());
        $analyticsData_360d = $this->fetchAnalyticDataAndSave($analytics, '360d', fn($today) => $today->subYear());
    }
}
