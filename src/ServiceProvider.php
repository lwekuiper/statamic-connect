<?php

namespace Lwekuiper\StatamicConnect;

use Lwekuiper\StatamicConnect\Data\AddonConfig;
use Lwekuiper\StatamicConnect\Fieldtypes\ConnectFormFields;
use Lwekuiper\StatamicConnect\Fieldtypes\ConnectMergeFields;
use Lwekuiper\StatamicConnect\Fieldtypes\ConnectRemoteList;
use Lwekuiper\StatamicConnect\Fieldtypes\ConnectRemoteTag;
use Lwekuiper\StatamicConnect\Fieldtypes\ConnectSites;
use Lwekuiper\StatamicConnect\Integrations\ActiveCampaign\ActiveCampaign;
use Lwekuiper\StatamicConnect\Listeners\EnsureFormConfigLocalizationsExist;
use Lwekuiper\StatamicConnect\Listeners\HandleFormSubmission;
use Lwekuiper\StatamicConnect\Stache\FormConfigRepository;
use Lwekuiper\StatamicConnect\Stache\FormConfigStore;
use Statamic\Events\FormSaved;
use Statamic\Events\SubmissionCreated;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Stache;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $fieldtypes = [
        ConnectFormFields::class,
        ConnectRemoteList::class,
        ConnectRemoteTag::class,
        ConnectMergeFields::class,
        ConnectSites::class,
    ];

    protected $listen = [
        SubmissionCreated::class => [
            HandleFormSubmission::class,
        ],
        FormSaved::class => [
            EnsureFormConfigLocalizationsExist::class,
        ],
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    protected $vite = [
        'input' => ['resources/js/addon.js'],
        'publicDirectory' => 'resources/dist',
        'hotFile' => 'resources/dist/hot',
    ];

    public function register(): void
    {
        parent::register();

        $this->app->singleton(IntegrationManager::class);
        $this->app->singleton(AddonConfig::class);
        $this->app->singleton(FormConfigRepository::class);
    }

    public function bootAddon(): void
    {
        $this->registerStache();
        $this->registerNavigation();
        $this->registerIntegrations();

        Statamic::afterInstalled(function () {
            $this->publishes([
                __DIR__.'/../config/connect.php' => config_path('statamic/connect.php'),
            ], 'connect-config');
        });
    }

    protected function registerStache(): void
    {
        $store = new FormConfigStore;
        $store->directory(base_path('content/connect/'));

        Stache::registerStore($store);
    }

    protected function registerNavigation(): void
    {
        Nav::extend(function ($nav) {
            $nav->tools('Connect')
                ->route('connect.index')
                ->icon('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>')
                ->children(function () {
                    $manager = app(IntegrationManager::class);

                    return $manager->all()->map(function ($integration) {
                        return Nav::item($integration->label())
                            ->url(cp_route('connect.index').'?integration='.$integration->name());
                    })->values()->all();
                });
        });
    }

    protected function registerIntegrations(): void
    {
        $manager = app(IntegrationManager::class);

        $manager->register(ActiveCampaign::class);
    }
}
