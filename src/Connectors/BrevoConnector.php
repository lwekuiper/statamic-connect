<?php

namespace Lwekuiper\StatamicConnect\Connectors;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class BrevoConnector extends BaseConnector
{
    protected function baseUrl(): string
    {
        return 'https://api.brevo.com/v3/';
    }

    protected function authenticatedClient(): PendingRequest
    {
        return Http::withHeaders([
            'api-key' => config('statamic.connect.integrations.brevo.api_key'),
        ])->acceptJson();
    }
}
