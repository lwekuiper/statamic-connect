<?php

namespace Lwekuiper\StatamicConnect\Tests;

use Illuminate\Support\Facades\Config;
use Lwekuiper\StatamicConnect\Data\FormConfig;
use Lwekuiper\StatamicConnect\ServiceProvider;
use Lwekuiper\StatamicConnect\Stache\FormConfigRepository;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Extend\Manifest;
use Statamic\Facades\Site;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Statamic;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;

    protected function resolveApplicationConfiguration($app): void
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('statamic.connect', require __DIR__.'/../config/connect.php');
    }

    protected function setProEdition(): void
    {
        Config::set('statamic.editions.addons.lwekuiper/statamic-connect', 'pro');
    }

    protected function setLiteEdition(): void
    {
        Config::set('statamic.editions.addons.lwekuiper/statamic-connect', 'lite');
    }

    protected function setSites(array $sites): void
    {
        Site::setSites(collect($sites)->mapWithKeys(fn ($site) => [
            $site['handle'] => $site,
        ])->all());
    }

    protected function createFormConfig(string $integration, string $form, string $site, array $data = []): FormConfig
    {
        $config = FormConfig::make()
            ->locale($site)
            ->integration($integration)
            ->form($form)
            ->data(collect($data));

        app(FormConfigRepository::class)->save($config);

        return $config;
    }
}
