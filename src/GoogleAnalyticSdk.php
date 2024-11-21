<?php

namespace FunnyDev\GoogleAnalytic;

class GoogleAnalyticSdk
{

    /**
     * @throws \Exception
     */
    public static function getReport(string $start_date='', string $end_date=''): array
    {
        $param = [
            'events' => [],
            'users' => [
                'countries' => [],
                'cities' => [],
                'gender' => [
                    'male' => 0,
                    'female' => 0,
                    'unknown' => 0
                ],
                'ages' => [
                    'unknown' => 0,
                    '18-24' => 0,
                    '25-34' => 0,
                    '35-44' => 0,
                    '45-54' => 0,
                    '55-64' => 0,
                    '65+' => 0
                ],
                'languages' => [],
                'favorites' => [],
                'channels' => [],
                'devices' => [
                    'types' => [],
                    'brands' => [],
                    'names' => [],
                    'models' => [],
                    'os' => [],
                    'versions' => [],
                    'browsers' => []
                ]
            ],
            'interaction' => [
                'average' => [
                    'session' => [],
                    'event' => [],
                ],
                'active' => [],
                'sources' => [],
                'pages' => [],
                'referrers' => [],
                'dau-mau' => [],
                'dau-wau' => [],
                'wau-mau' => []
            ],
            'retention-rate' => [
                'new' => [],
                'returning' => [],
                'evolution' => [],
                'engagement' => []
            ],
            'business' => [
                'products' => [
                    'viewed' => 0,
                    'added' => 0,
                    'purchased' => 0,
                    'revenue' => 0
                ],
                'promotional' => [],
            ]
        ];

        $instance = new GoogleAnalyticReportHelper(start_date: $start_date, end_date: $end_date);

        $instance->setDimension('eventName');
        $instance->setMetric('totalUsers');
        $events_users = $instance->getReport() ?? [];
        $instance->setMetric('eventCount');
        $events_count = $instance->getReport() ?? [];
        $events_keys = [];
        if (!empty($events_users)) {
            $events_keys = array_merge(array_keys($events_users));
        }
        if (!empty($events_count)) {
            $events_keys = array_merge(array_keys($events_count));
        }
        if (!empty($events_keys)) {
            foreach ($events_keys as $key) {
                $param['events'][$key] = [
                    'users' => $events_users[$key] ?? 0,
                    'counts' => $events_count[$key] ?? 0,
                ];
            }
        }

        $instance->setMetric('totalUsers');
        $instance->setDimension('countryId');
        $param['users']['countries'] = $instance->getReport(sortBy: 'value') ?? [];

        $instance->setDimension('city');
        $param['users']['cities'] = $instance->getReport(sortBy: 'value') ?? [];

        $instance->setDimension('userGender');
        $data = $instance->getReport() ?? false;
        if ($data) {
            foreach (['male', 'female', 'unknown'] as $key) {
                $param['users']['gender'][$key] = $data[$key] ?? 0;
            }
        }

        $instance->setDimension('userAgeBracket');
        $data = $instance->getReport() ?? false;
        if ($data) {
            foreach (['unknown', '18-24', '25-34', '35-44', '45-54', '55-64', '65+'] as $key) {
                $param['users']['ages'][$key] = $data[$key] ?? 0;
            }
        }

        $instance->setDimension('language');
        $param['users']['languages'] = $instance->getReport(sortBy: 'value') ?? [];

        $instance->setDimension('brandingInterest');
        $param['users']['favorites'] = $instance->getReport(sortBy: 'value') ?? [];

        $instance->setDimension('sessionDefaultChannelGroup');
        $param['users']['channels'] = $instance->getReport(sortBy: 'value') ?? [];

        $instance->setDimension('deviceCategory');
        $param['users']['devices']['types'] = $instance->getReport(sortBy: 'value') ?? [];

        $instance->setDimension('mobileDeviceBranding');
        $param['users']['devices']['brands'] = $instance->getReport(sortBy: 'value') ?? [];

        $instance->setDimension('mobileDeviceMarketingName');
        $param['users']['devices']['names'] = $instance->getReport(sortBy: 'value') ?? [];

        $instance->setDimension('mobileDeviceModel');
        $param['users']['devices']['models'] = $instance->getReport(sortBy: 'value') ?? [];

        $instance->setDimension('operatingSystem');
        $param['users']['devices']['os'] = $instance->getReport(sortBy: 'value') ?? [];

        $instance->setDimension('operatingSystemWithVersion');
        $param['users']['devices']['versions'] = $instance->getReport(sortBy: 'value') ?? [];

        $instance->setDimension('browser');
        $param['users']['devices']['browsers'] = $instance->getReport(sortBy: 'value') ?? [];

        $instance->setDimension('nthDay');
        $instance->setMetric('sessionsPerUser');
        $param['interaction']['average']['session'] = $instance->calculate_average_values($instance->getReport()) ?? 0;

        $instance->setMetric('eventCountPerUser');
        $param['interaction']['average']['event'] = $instance->calculate_average_values($instance->getReport()) ?? 0;

        $instance->setMetric('activeUsers');
        $param['interaction']['active'] = $instance->add_date_values($instance->getReport(), $instance->endDate) ?? [];

        $instance->setDimension('sessionSource');
        $instance->setMetric('engagedSessions');
        $param['interaction']['sources'] = $instance->getReport(sortBy: 'value') ?? [];

        $instance->setDimension('pageLocation');
        $param['interaction']['pages'] = $instance->remove_query_keys($instance->getReport(sortBy: 'value')) ?? [];

        $instance->setDimension('pageReferrer');
        $param['interaction']['referrers'] = $instance->remove_query_keys($instance->getReport(sortBy: 'value')) ?? [];

        $instance->setDimension('nthDay');
        $instance->setMetric('dauPerMau');
        $param['interaction']['dau-mau'] = $instance->add_date_values($instance->getReport(), $instance->endDate) ?? [];

        $instance->setMetric('dauPerWau');
        $param['interaction']['dau-wau'] = $instance->add_date_values($instance->getReport(), $instance->endDate) ?? [];

        $instance->setMetric('wauPerMau');
        $param['interaction']['wau-mau'] = $instance->add_date_values($instance->getReport(), $instance->endDate) ?? [];

        $instance->setDimension('newVsReturning');
        $instance->setMetric('totalUsers');
        $data = $instance->getReport() ?? [];
        if ($data) {
            $param['retention-rate']['new'] = $data['new'] ?? 0;
            $param['retention-rate']['returning'] = $data['returning'] ?? 0;
        }

        $instance->setDimension('nthDay');
        $instance->setMetric('totalUsers');
        $param['retention-rate']['evolution'] = $instance->add_date_values($instance->getReport(), $instance->endDate) ?? [];

        $instance->setMetric('engagementRate');
        $param['retention-rate']['engagement'] = $instance->add_date_values($instance->getReport(), $instance->endDate) ?? [];

        $instance->setDimension('itemName');
        $instance->setMetric('itemsViewed');
        $param['business']['products']['viewed'] = $instance->getReport(sortBy: 'value') ?? [];

        $instance->setMetric('itemsAddedToCart');
        $param['business']['products']['added'] = $instance->getReport(sortBy: 'value') ?? [];

        $instance->setMetric('itemsPurchased');
        $param['business']['products']['purchased'] = $instance->getReport(sortBy: 'value') ?? [];

        $instance->setMetric('itemRevenue');
        $param['business']['products']['revenue'] = $instance->getReport(sortBy: 'value') ?? [];

        $instance->setDimension('itemPromotionName');
        $instance->setMetric('itemsViewedInPromotion');
        $param['business']['promotion'] = $instance->getReport(sortBy: 'value') ?? [];

        return $param;
    }

    /**
     * @throws \Exception
     */
    public static function sendReport(string $client_id='', string $name='custom', array $params=[], string $measurement_id='', string $measurement_secret_key='', array $credentials=null, string $credentials_path=null): bool
    {
        $instance = new GoogleAnalyticMeasurementHelper(measurement_id: $measurement_id, measurement_secret_key: $measurement_secret_key, credentials: $credentials, credentials_path: $credentials_path);
        return $instance->send($client_id, $name, $params);
    }
}
