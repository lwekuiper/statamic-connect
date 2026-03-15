<?php

namespace Lwekuiper\StatamicConnect\Tests;

use Illuminate\Foundation\Testing\WithFaker;
use Lwekuiper\StatamicConnect\ServiceProvider;
use Statamic\Facades\Addon;
use Statamic\Facades\Config;
use Statamic\Facades\Site;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    use WithFaker;

    protected string $addonServiceProvider = ServiceProvider::class;

    protected function setSites($sites)
    {
        Site::setSites($sites);

        Config::set('statamic.system.multisite', Site::hasMultiple());
    }

    protected function setProEdition()
    {
        Config::set('statamic.editions.addons.lwekuiper/statamic-connect', 'pro');
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        Addon::get('lwekuiper/statamic-connect')->editions(['lite', 'pro']);
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $app['config']->set('statamic.editions.pro', true);
    }

    protected function assertEveryItemIsInstanceOf($class, $items)
    {
        if ($items instanceof \Illuminate\Support\Collection) {
            $items = $items->all();
        }

        $matches = 0;

        foreach ($items as $item) {
            if ($item instanceof $class) {
                $matches++;
            }
        }

        $this->assertEquals(count($items), $matches, 'Failed asserting that every item is an instance of '.$class);
    }
}
