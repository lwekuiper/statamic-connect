<?php

namespace Lwekuiper\StatamicConnect\Integrations;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Statamic\Facades\Blink;

abstract class BaseConnector
{
    abstract protected function baseUrl(): string;

    abstract protected function headers(): array;

    protected function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl())
            ->withHeaders($this->headers())
            ->acceptJson()
            ->asJson();
    }

    protected function get(string $endpoint, array $params = []): ?array
    {
        $cacheKey = $this->cacheKey('get', $endpoint, $params);

        return Blink::once($cacheKey, function () use ($endpoint, $params) {
            $response = $this->client()->get($endpoint, $params);

            return $this->handleResponse($response, "GET {$endpoint}");
        });
    }

    protected function post(string $endpoint, array $data = []): ?array
    {
        $response = $this->client()->post($endpoint, $data);

        return $this->handleResponse($response, "POST {$endpoint}");
    }

    protected function put(string $endpoint, array $data = []): ?array
    {
        $response = $this->client()->put($endpoint, $data);

        return $this->handleResponse($response, "PUT {$endpoint}");
    }

    protected function delete(string $endpoint): ?array
    {
        $response = $this->client()->delete($endpoint);

        return $this->handleResponse($response, "DELETE {$endpoint}");
    }

    protected function handleResponse(Response $response, string $context): ?array
    {
        if ($response->successful()) {
            return $response->json();
        }

        Log::error("[Connect] API request failed: {$context}", [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return null;
    }

    protected function cacheKey(string $method, string $endpoint, array $params = []): string
    {
        return 'connect:'.static::class.':'.$method.':'.$endpoint.':'.md5(serialize($params));
    }
}
