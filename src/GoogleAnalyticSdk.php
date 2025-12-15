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
    public static function sendReport(
        string $client_id = '',
        string $name = 'custom',
        array $params = [],
        string $measurement_id = '',
        string $measurement_api_secret = '',
        array $credentials = null,
        string $credentials_path = null,
        ?string $user_id = null,
        bool $debug = false,
        array $user_properties = [],
        ?int $timestamp_micros = null,
        bool $use_debug_endpoint = false,
    ): bool
    {
        $instance = new GoogleAnalyticMeasurementHelper(measurement_id: $measurement_id, measurement_api_secret: $measurement_api_secret, credentials: $credentials, credentials_path: $credentials_path);
        return $instance->send($client_id, $name, $params, $user_id, $debug, $user_properties, $timestamp_micros, $use_debug_endpoint);
    }

    /**
     * Resolve GA4 identifiers from current request and Laravel Auth.
     *
     * - client_id: parsed from _ga cookie if available.
     * - user_id: Auth::user()->uuid when available.
     */
    public static function resolveIdsFromRequest(): array
    {
        $clientId = '';
        $userId = null;

        // Try parse _ga cookie → GA1.1.1234567890.1234567890
        try {
            $cookie = request()->cookie('_ga');
            if (!empty($cookie)) {
                // Expect segments like GA1.1.XXXX.YYYY → take last two parts
                $segments = explode('.', $cookie);
                if (count($segments) >= 4) {
                    $candidate = $segments[count($segments)-2] . '.' . $segments[count($segments)-1];
                    if (preg_match('/^\d+\.\d+$/', $candidate)) {
                        $clientId = $candidate;
                    }
                }
            }
        } catch (\Throwable) {
            // ignore
        }

        // Use Laravel Auth (if available) to set user_id
        $userId = self::resolveUserIdFromRequest();

        return [$clientId, $userId];
    }

    public static function resolveUserIdFromRequest(): ?string
    {
        try {
            if (!\Illuminate\Support\Facades\Auth::check()) {
                return null;
            }

            $user = \Illuminate\Support\Facades\Auth::user();
            $resolver = config('google-analytic.user_id_resolver');
            if (is_callable($resolver)) {
                $id = $resolver($user, request());
                return $id !== null && $id !== '' ? (string) $id : null;
            }

            $fields = config('google-analytic.user_id_fields', ['uuid', 'id']);
            if (!is_array($fields)) {
                $fields = ['uuid', 'id'];
            }

            foreach ($fields as $field) {
                if (is_string($field) && $field !== '' && isset($user->{$field})) {
                    $value = $user->{$field};
                    if ($value !== null && $value !== '') {
                        return (string) $value;
                    }
                }
            }

            // Fallback to Auth identifier
            try {
                $fallback = $user->getAuthIdentifier();
                return $fallback !== null && $fallback !== '' ? (string) $fallback : null;
            } catch (\Throwable) {
                return null;
            }
        } catch (\Throwable) {
            return null;
        }
    }

    public static function resolveUserPropertiesFromRequest(): array
    {
        try {
            $user = null;
            if (\Illuminate\Support\Facades\Auth::check()) {
                $user = \Illuminate\Support\Facades\Auth::user();
            }

            $resolver = config('google-analytic.user_properties_resolver');
            if (is_callable($resolver)) {
                $properties = $resolver($user, request());
                return is_array($properties) ? $properties : [];
            }

            if ($user === null) {
                return [];
            }

            $mapping = config('google-analytic.user_properties', []);
            if (!is_array($mapping) || empty($mapping)) {
                return [];
            }

            $out = [];
            foreach ($mapping as $propertyName => $path) {
                if (!is_string($propertyName) || $propertyName === '') {
                    continue;
                }

                if (is_callable($path)) {
                    $value = $path($user, request());
                } elseif (is_string($path) && $path !== '') {
                    $value = data_get($user, $path);
                } else {
                    $value = null;
                }

                if ($value === null || $value === '') {
                    continue;
                }
                $out[$propertyName] = $value;
            }

            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Send a standardized GA4 e-commerce add_to_cart event.
     */
    public static function eventAddToCart(array $items, float $value, string $currency = 'USD', array $extraParams = [], ?string $client_id = null, ?string $user_id = null, bool $debug = false, array $user_properties = []): bool
    {
        [$resolvedClientId, $resolvedUserId] = self::resolveIdsFromRequest();
        $resolvedUserProperties = self::resolveUserPropertiesFromRequest();
        $params = array_merge([
            'currency' => $currency,
            'value' => $value,
            'items' => $items,
        ], $extraParams);

        return self::sendReport(
            client_id: $client_id ?? $resolvedClientId,
            name: 'add_to_cart',
            params: $params,
            user_id: $user_id ?? $resolvedUserId,
            debug: $debug,
            user_properties: !empty($user_properties) ? $user_properties : $resolvedUserProperties,
        );
    }

    /**
     * Send a standardized GA4 begin_checkout event.
     */
    public static function eventBeginCheckout(array $items, float $value, string $currency = 'USD', array $extraParams = [], ?string $client_id = null, ?string $user_id = null, bool $debug = false, array $user_properties = []): bool
    {
        [$resolvedClientId, $resolvedUserId] = self::resolveIdsFromRequest();
        $resolvedUserProperties = self::resolveUserPropertiesFromRequest();
        $params = array_merge([
            'currency' => $currency,
            'value' => $value,
            'items' => $items,
        ], $extraParams);

        return self::sendReport(
            client_id: $client_id ?? $resolvedClientId,
            name: 'begin_checkout',
            params: $params,
            user_id: $user_id ?? $resolvedUserId,
            debug: $debug,
            user_properties: !empty($user_properties) ? $user_properties : $resolvedUserProperties,
        );
    }

    /**
     * Send a standardized GA4 purchase event.
     */
    public static function eventPurchase(string $transaction_id, array $items, float $value, string $currency = 'USD', array $extraParams = [], ?string $client_id = null, ?string $user_id = null, bool $debug = false, array $user_properties = []): bool
    {
        if (trim($transaction_id) === '' || empty($items)) {
            return false;
        }
        [$resolvedClientId, $resolvedUserId] = self::resolveIdsFromRequest();
        $resolvedUserProperties = self::resolveUserPropertiesFromRequest();
        $params = array_merge([
            'transaction_id' => $transaction_id,
            'currency' => $currency,
            'value' => $value,
            'items' => $items,
        ], $extraParams);

        return self::sendReport(
            client_id: $client_id ?? $resolvedClientId,
            name: 'purchase',
            params: $params,
            user_id: $user_id ?? $resolvedUserId,
            debug: $debug,
            user_properties: !empty($user_properties) ? $user_properties : $resolvedUserProperties,
        );
    }

    /**
     * Send a standardized GA4 sign_up event.
     */
    public static function eventSignUp(string $method = 'email', array $extraParams = [], ?string $client_id = null, ?string $user_id = null, bool $debug = false, array $user_properties = []): bool
    {
        [$resolvedClientId, $resolvedUserId] = self::resolveIdsFromRequest();
        $resolvedUserProperties = self::resolveUserPropertiesFromRequest();
        $params = array_merge([
            'method' => $method,
        ], $extraParams);

        return self::sendReport(
            client_id: $client_id ?? $resolvedClientId,
            name: 'sign_up',
            params: $params,
            user_id: $user_id ?? $resolvedUserId,
            debug: $debug,
            user_properties: !empty($user_properties) ? $user_properties : $resolvedUserProperties,
        );
    }
}
