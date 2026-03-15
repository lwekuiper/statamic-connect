<?php

namespace Lwekuiper\StatamicConnect\Data;

use Illuminate\Support\Collection;
use Statamic\Facades\File;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;

class AddonConfig
{
    protected array $data = [];

    protected string $path;

    public function __construct()
    {
        $this->path = base_path('content/connect/config.yaml');
        $this->load();
    }

    protected function load(): void
    {
        if (File::exists($this->path)) {
            $this->data = YAML::file($this->path)->parse();
        }
    }

    public function path(): string
    {
        return $this->path;
    }

    public function isEnabled(?string $site, string $integration): bool
    {
        $site = $site ?? Site::default()->handle();

        return in_array($integration, $this->enabledIntegrations($site)->all());
    }

    public function enabledIntegrations(?string $site = null): Collection
    {
        $site = $site ?? Site::default()->handle();

        return collect($this->data['sites'][$site]['integrations'] ?? []);
    }

    public function setEnabledIntegrations(string $site, array $integrations): self
    {
        $this->data['sites'][$site]['integrations'] = $integrations;

        return $this;
    }

    public function save(): void
    {
        File::put($this->path, YAML::dump($this->data));
    }

    public function exists(): bool
    {
        return File::exists($this->path);
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
