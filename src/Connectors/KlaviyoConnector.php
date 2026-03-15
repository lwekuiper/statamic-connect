<?php

namespace Lwekuiper\StatamicConnect\Connectors;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class KlaviyoConnector extends BaseConnector
{
    protected function baseUrl(): string
    {
        return 'https://a.klaviyo.com/api/';
    }

    protected function authenticatedClient(): PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => 'Klaviyo-API-Key '.config('statamic.connect.integrations.klaviyo.api_key'),
            'revision' => '2024-02-15',
        ])->acceptJson();
    }
}
