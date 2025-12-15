<?php

namespace FunnyDev\GoogleAnalytic;

use Illuminate\Support\Facades\Http;

class GoogleAnalyticMeasurementHelper
{
    public string $endpoint;
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

        $this->endpoint = 'https://www.google-analytics.com/mp/collect' . '?' . http_build_query(['measurement_id' => $this->measurement_id, 'api_secret' => $this->measurement_api_secret]);
    }

    /**
     * @throws \Exception
     */
    public function send(string $client_id = '', string $name = 'custom', array $params = [], ?string $user_id = null, bool $debug = false): bool
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

        $response = Http::post($this->endpoint, $payload);

        return $response->successful();
    }
}
