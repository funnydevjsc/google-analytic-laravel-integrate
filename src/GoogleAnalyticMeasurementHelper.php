<?php

namespace FunnyDev\GoogleAnalytic;

use Illuminate\Support\Facades\Http;

class GoogleAnalyticMeasurementHelper
{
    public string $endpoint;
    public string $debugEndpoint;
    public string $measurement_id;
    public string $measurement_api_secret;

    /**
     * @throws \Exception
     */
    public function __construct(string $measurement_id='', string $measurement_api_secret='', array $credentials=null, string $credentials_path=null)
    {
        if ($measurement_id !== '') {
            $this->measurement_id = $measurement_id;
        } else {
            $this->measurement_id = config('google-analytic.measurement_id');
        }

        if ($measurement_api_secret !== '') {
            $this->measurement_api_secret = $measurement_api_secret;
        } else {
            $this->measurement_api_secret = config('google-analytic.measurement_api_secret');
        }

        $query = http_build_query(['measurement_id' => $this->measurement_id, 'api_secret' => $this->measurement_api_secret]);
        $this->endpoint = 'https://www.google-analytics.com/mp/collect' . '?' . $query;
        $this->debugEndpoint = 'https://www.google-analytics.com/debug/mp/collect' . '?' . $query;
    }

    /**
     * @throws \Exception
     */
    public function send(
        string $client_id = '',
        string $name = 'custom',
        array $params = [],
        ?string $user_id = null,
        bool $debug = false,
        array $user_properties = [],
        ?int $timestamp_micros = null,
        bool $use_debug_endpoint = false,
    ): bool
    {
        // Build event payload
        $eventParams = $params;
        if ($debug) {
            $eventParams['debug_mode'] = true;
        }

        $payload = [
            'events' => [
                [
                    'name' => $name,
                    'params' => $eventParams,
                ],
            ],
        ];

        if ($timestamp_micros !== null) {
            $payload['timestamp_micros'] = $timestamp_micros;
        }

        if (!empty($user_properties)) {
            $normalizedUserProperties = [];
            foreach ($user_properties as $key => $value) {
                if ($value === null || $value === '') {
                    continue;
                }
                if (is_array($value) && array_key_exists('value', $value)) {
                    $normalizedUserProperties[$key] = $value;
                } else {
                    $normalizedUserProperties[$key] = ['value' => $value];
                }
            }
            if (!empty($normalizedUserProperties)) {
                $payload['user_properties'] = $normalizedUserProperties;
            }
        }

        // Determine valid identifiers according to GA4 Measurement Protocol
        $clientId = trim($client_id);
        $isLikelyClientId = (bool) preg_match('/^\d+\.\d+$/', $clientId);

        if ($isLikelyClientId) {
            $payload['client_id'] = $clientId;
        }

        // Use provided user_id or fall back to a non-client-id string (e.g. UUID)
        $userId = $user_id;
        if ($userId === null && $clientId !== '' && !$isLikelyClientId) {
            $userId = $clientId; // backward compatible: earlier versions passed UUID as client_id
        }
        if (!empty($userId)) {
            $payload['user_id'] = $userId;
        }

        $endpoint = $use_debug_endpoint ? $this->debugEndpoint : $this->endpoint;
        $response = Http::post($endpoint, $payload);

        return $response->successful();
    }
}
