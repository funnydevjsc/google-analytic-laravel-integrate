<?php

namespace FunnyDev\GoogleAnalytic;

use Illuminate\Support\Facades\Http;

class GoogleAnalyticMeasurementHelper
{
    public string $endpoint;
    public string $measurement_id;
    public string $measurement_secret_key;
    public GoogleServiceClient $client;

    /**
     * @throws \Exception
     */
    public function __construct(string $measurement_id='', string $measurement_secret_key='', array $credentials=null, string $credentials_path=null)
    {
        if ($measurement_id !== '') {
            $this->measurement_id = $measurement_id;
        } else {
            $this->measurement_id = config('google-analytic.measurement_id');
        }

        if ($measurement_secret_key !== '') {
            $this->measurement_secret_key = $measurement_secret_key;
        } else {
            $this->measurement_secret_key = config('google-analytic.measurement_secret_key');
        }

        $this->endpoint = 'https://www.google-analytics.com/mp/collect' . '?' . http_build_query(['measurement_id' => $this->measurement_id, 'api_secret' => $this->measurement_secret_key]);

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
            $payload = array_merge($payload, ['client_id' => $client_id, 'user_properties' => ['user_id' => $client_id]]);
        }

        $response = Http::post($this->endpoint, $payload);

        return $response->successful();
    }
}
