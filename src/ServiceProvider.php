<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicConnect;

use Lwekuiper\StatamicConnect\Connectors\ActiveCampaignConnector;
use Lwekuiper\StatamicConnect\Connectors\HubSpotConnector;
use Lwekuiper\StatamicConnect\Data\AddonConfig;
use Lwekuiper\StatamicConnect\Fieldtypes\ActivecampaignList;
use Lwekuiper\StatamicConnect\Fieldtypes\ActivecampaignMergeFields;
use Lwekuiper\StatamicConnect\Fieldtypes\ActivecampaignTag;
use Lwekuiper\StatamicConnect\Fieldtypes\ConnectSites;
use Lwekuiper\StatamicConnect\Fieldtypes\HubspotContactProperties;
use Lwekuiper\StatamicConnect\Fieldtypes\StatamicFormFields;
use Lwekuiper\StatamicConnect\Listeners\DispatchToIntegrations;
use Lwekuiper\StatamicConnect\Listeners\EnsureFormConfigLocalizationsExist;
use Lwekuiper\StatamicConnect\Stache\FormConfigRepository;
use Lwekuiper\StatamicConnect\Stache\FormConfigStore;
use Statamic\Events\FormSaved;
use Statamic\Events\SubmissionCreated;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Form;
use Statamic\Facades\Site;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Stache\Stache;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $fieldtypes = [
        ConnectSites::class,
        StatamicFormFields::class,
        ActivecampaignList::class,
        ActivecampaignMergeFields::class,
        ActivecampaignTag::class,
        HubspotContactProperties::class,
    ];

    protected $listen = [
        FormSaved::class => [EnsureFormConfigLocalizationsExist::class],
        SubmissionCreated::class => [DispatchToIntegrations::class],
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $vite = [
        'input' => ['resources/js/addon.js'],
        'publicDirectory' => 'resources/dist',
        'hotFile' => 'resources/dist/hot',
    ];

    public function register()
    {
        $this->app->singleton(FormConfigRepository::class, function () {
            return new FormConfigRepository($this->app['stache']);
        });

        $this->app->singleton(ActiveCampaignConnector::class, function () {
            return new ActiveCampaignConnector;
        });

        $this->app->singleton(HubSpotConnector::class, function () {
            return new HubSpotConnector;
        });

        $this->app->singleton(AddonConfig::class, function () {
            return new AddonConfig;
        });

        $this->publishes([
            __DIR__.'/../config/connect.php' => config_path('statamic/connect.php'),
        ], 'connect-config');
    }

    public function bootAddon()
    {
        $this->registerNavigation();
        $this->registerStacheStores();

        Statamic::afterInstalled(function ($command) {
            $command->call('vendor:publish', [
                '--tag' => 'connect-config',
            ]);
        });
    }

    private function registerNavigation(): void
    {
        Nav::extend(function ($nav) {
            $siteEnabled = app(AddonConfig::class)->isEnabled(Site::selected()->handle());

            $nav->create('Connect')
                ->section('Tools')
                ->url($siteEnabled
                    ? cp_route('connect.activecampaign.index')
                    : cp_route('connect.settings.edit'))
                ->can('index', Form::class)
                ->icon('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>')
                ->children(function () use ($siteEnabled) {
                    if (! $siteEnabled) {
                        return collect();
                    }

                    $items = collect();

                    // ActiveCampaign section
                    $items->push(Nav::item('ActiveCampaign')
                        ->url(cp_route('connect.activecampaign.index')));

                    // HubSpot section
                    $items->push(Nav::item('HubSpot')
                        ->url(cp_route('connect.hubspot.index')));

                    // Settings
                    $items->push(Nav::item(__('Settings'))
                        ->url(cp_route('connect.settings.edit')));

                    return $items;
                });
        });
    }

    private function registerStacheStores(): void
    {
        $integrations = ['activecampaign', 'hubspot'];
        $repository = app(FormConfigRepository::class);

        foreach ($integrations as $integration) {
            $store = new FormConfigStore($integration);
            $store->directory(base_path("resources/connect/{$integration}"));
            app(Stache::class)->registerStore($store);
            $repository->registerStore($integration);
        }
    }
}
