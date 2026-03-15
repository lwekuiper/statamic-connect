<?php

namespace Lwekuiper\StatamicConnect;

use Illuminate\Support\Collection;
use Lwekuiper\StatamicConnect\Integrations\BaseIntegration;

class IntegrationManager
{
    protected array $integrations = [];

    public function register(string $class): void
    {
        $integration = app($class);

        $this->integrations[$integration->name()] = $integration;
    }

    public function all(): Collection
    {
        return collect($this->integrations);
    }

    public function find(string $name): ?BaseIntegration
    {
        return $this->integrations[$name] ?? null;
    }

    public function enabled(?string $site = null): Collection
    {
        $addonConfig = app(Data\AddonConfig::class);

        return $this->all()->filter(function (BaseIntegration $integration) use ($addonConfig, $site) {
            return $addonConfig->isEnabled($site, $integration->name())
                && $integration->isConfigured();
        });
    }
}
