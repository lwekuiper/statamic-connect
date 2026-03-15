<?php

namespace Lwekuiper\StatamicConnect\Connectors;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class BaseConnector
{
    abstract protected function baseUrl(): string;

    abstract protected function authenticatedClient(): PendingRequest;

    protected function client(): PendingRequest
    {
        return $this->authenticatedClient()->baseUrl($this->baseUrl());
    }

    protected function handleResponse(Response $response): mixed
    {
        if ($response->failed()) {
            Log::error(static::class.' API error', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return null;
        }

        return $response->json();
    }
}
