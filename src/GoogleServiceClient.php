<?php

namespace FunnyDev\GoogleAnalytic;

use Google\Client;

class GoogleServiceClient
{
    private array $credentials;
    private string $credentials_path;
    public Client $client;

    public function __construct(array $credentials=null, string $credentials_path=null)
    {
        if ($credentials) {
            $this->credentials = $credentials;
        } else {
            $this->credentials = [
                'type' => 'service_account',
                'project_id' => config('google-service.project_id'),
                'private_key_id' => config('google-service.private_key_id'),
                'private_key' => str_replace("\\n", "\n", config('google-service.private_key')),
                'client_email' => config('google-service.client_email'),
                'client_id' => config('google-service.client_id'),
                'auth_uri' => 'https://accounts.google.com/o/oauth2/auth',
                'token_uri' => 'https://oauth2.googleapis.com/token',
                'auth_provider_x509_cert_url' => 'https://www.googleapis.com/oauth2/v1/certs',
                'client_x509_cert_url' => 'https://www.googleapis.com/robot/v1/metadata/x509/'.urlencode(config('google-service.client_email')),
                'developer_key' => config('google-service.developer_key')
            ];
        }
        if ($credentials_path) {
            $this->credentials_path = $credentials_path;
        } else {
            $this->credentials_path = storage_path('application_default_credentials.json');
        }
    }

    /**
     * @throws \Exception
     */
    public function instance(): Client
    {
        $options = ['credentials' => $this->credentials, 'projectId' => config('google-service.project_id')];
        $client = new Client($options);
        $client->useApplicationDefaultCredentials(false);
        $client->setAuthConfig($this->credentials_path);
        $client->addScope('https://www.googleapis.com/auth/analytics.readonly');

        return $client;
    }
}
