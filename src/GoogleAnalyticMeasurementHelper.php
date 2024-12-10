<?php

namespace FunnyDev\GoogleAnalytic;

use Illuminate\Support\Facades\Http;

class GoogleAnalyticMeasurementHelper
{
    public string $endpoint;
    public string $measurement_id;
    public string $measurement_api_secret;
    public GoogleServiceClient $client;

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

        $this->client = new GoogleServiceClient($credentials, $credentials_path);
    }

    /**
     * @throws \Exception
     */
    public function send(string $client_id='', string $name='custom', array $params=[]): bool
    {
        $payload = [
            'events' => [
                [
                    'name' => $name,
                    'params' => $params
                ],
            ]
        ];

        if (!empty($client_id)) {
            $payload = array_merge($payload, ['client_id' => $client_id, 'user_data' => ['user_id' => $client_id]]);
        }

        $response = Http::post($this->endpoint, $payload);

        return $response->successful();
    }
}
