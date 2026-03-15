<?php

namespace Lwekuiper\StatamicConnect\Stache;

use Illuminate\Support\Collection;
use Lwekuiper\StatamicConnect\Data\FormConfig;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;

class FormConfigRepository
{
    public function all(): Collection
    {
        return Stache::store('connect-form-configs')->getItems();
    }

    public function find(string $form, string $integration, ?string $locale = null): ?FormConfig
    {
        $locale = $locale ?? Site::default()->handle();
        $id = "{$locale}::{$integration}::{$form}";

        return $this->all()->get($id);
    }

    public function findResolved(string $form, string $integration, ?string $locale = null): ?FormConfig
    {
        $config = $this->find($form, $integration, $locale);

        if ($config) {
            return $config;
        }

        // Fall back to the default site's config as the origin
        $defaultLocale = Site::default()->handle();

        if ($locale !== $defaultLocale) {
            return $this->find($form, $integration, $defaultLocale);
        }

        return null;
    }

    public function forForm(string $form, ?string $locale = null): Collection
    {
        return $this->all()->filter(function (FormConfig $config) use ($form, $locale) {
            if ($config->form() !== $form) {
                return false;
            }

            if ($locale && $config->locale() !== $locale) {
                return false;
            }

            return true;
        });
    }

    public function forIntegration(string $integration, ?string $locale = null): Collection
    {
        return $this->all()->filter(function (FormConfig $config) use ($integration, $locale) {
            if ($config->integration() !== $integration) {
                return false;
            }

            if ($locale && $config->locale() !== $locale) {
                return false;
            }

            return true;
        });
    }

    public function save(FormConfig $config): void
    {
        Stache::store('connect-form-configs')->save($config);
    }

    public function delete(FormConfig $config): void
    {
        Stache::store('connect-form-configs')->delete($config);
    }
}
