<?php

namespace Lwekuiper\StatamicConnect\Connectors;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class SalesforceConnector extends BaseConnector
{
    protected function baseUrl(): string
    {
        return config('statamic.connect.integrations.salesforce.instance_url', '').'/services/data/v59.0/';
    }

    protected function authenticatedClient(): PendingRequest
    {
        // TODO: Implement OAuth2 token exchange
        return Http::acceptJson();
    }
}
