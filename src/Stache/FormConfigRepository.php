<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicConnect\Stache;

use Lwekuiper\StatamicConnect\Data\AddonConfig;
use Lwekuiper\StatamicConnect\Data\FormConfig;
use Lwekuiper\StatamicConnect\Data\FormConfigCollection;
use Lwekuiper\StatamicConnect\Exceptions\FormConfigNotFoundException;
use Statamic\Stache\Stache;

class FormConfigRepository
{
    protected Stache $stache;

    protected array $stores = [];

    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
    }

    public function registerStore(string $integration): void
    {
        $this->stores[$integration] = $integration.'-form-configs';
    }

    protected function store(string $integration)
    {
        return $this->stache->store($integration.'-form-configs');
    }

    public function make(): FormConfig
    {
        return new FormConfig;
    }

    public function all(?string $integration = null): FormConfigCollection
    {
        if ($integration) {
            $keys = $this->store($integration)->paths()->keys();

            return FormConfigCollection::make($this->store($integration)->getItems($keys));
        }

        $items = collect();

        foreach ($this->stores as $name => $storeKey) {
            $store = $this->stache->store($storeKey);
            $keys = $store->paths()->keys();
            $items = $items->merge($store->getItems($keys));
        }

        return FormConfigCollection::make($items);
    }

    public function find(string $integration, string $form, string $site): ?FormConfig
    {
        return $this->store($integration)->getItem("{$integration}::{$form}::{$site}");
    }

    public function findOrFail(string $integration, string $form, string $site): FormConfig
    {
        $formConfig = $this->find($integration, $form, $site);

        if (! $formConfig) {
            throw new FormConfigNotFoundException("{$integration}::{$form}::{$site}");
        }

        return $formConfig;
    }

    public function findResolved(string $integration, string $form, string $site): ?FormConfig
    {
        if ($config = $this->find($integration, $form, $site)) {
            return $config;
        }

        $origin = app(AddonConfig::class)->originFor($site);

        return $origin ? $this->findResolved($integration, $form, $origin) : null;
    }

    public function whereIntegration(string $integration): FormConfigCollection
    {
        $keys = $this->store($integration)->paths()->keys();

        return FormConfigCollection::make($this->store($integration)->getItems($keys));
    }

    public function whereForm(string $integration, $handle): FormConfigCollection
    {
        $keys = $this->store($integration)
            ->index('handle')
            ->items()
            ->filter(fn ($value) => $value == $handle)
            ->keys();

        $items = $this->store($integration)->getItems($keys)->filter(fn ($item) => $item->site());

        return FormConfigCollection::make($items);
    }

    public function whereLocale(string $integration, $site): FormConfigCollection
    {
        $keys = $this->store($integration)
            ->index('locale')
            ->items()
            ->filter(fn ($value) => $value == $site)
            ->keys();

        return FormConfigCollection::make($this->store($integration)->getItems($keys));
    }

    public function ensureLocalizationsExist(string $integration, string $formHandle): void
    {
        $enabledSites = app(AddonConfig::class)->sites()->keys();

        $enabledSites->each(function ($siteHandle) use ($integration, $formHandle) {
            if (! $this->find($integration, $formHandle, $siteHandle)) {
                $this->make()->integration($integration)->form($formHandle)->locale($siteHandle)->save();
            }
        });
    }

    public function save(FormConfig $formConfig): bool
    {
        $this->store($formConfig->integration())->save($formConfig);

        return true;
    }

    public function delete(FormConfig $formConfig): bool
    {
        $this->store($formConfig->integration())->delete($formConfig);

        return true;
    }
}
