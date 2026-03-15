<?php

namespace Lwekuiper\StatamicConnect\Stache;

use Lwekuiper\StatamicConnect\Data\FormConfig;
use Statamic\Facades\YAML;
use Statamic\Stache\Stores\BasicStore;
use Symfony\Component\Finder\SplFileInfo;

class FormConfigStore extends BasicStore
{
    public function key(): string
    {
        return 'connect-form-configs';
    }

    public function makeItemFromFile($path, $contents): FormConfig
    {
        $data = YAML::file($path)->parse($contents);
        $relative = str_after($path, $this->directory());

        // Path structure: {locale}/{integration}/{form}.yaml
        $parts = explode('/', $relative);

        if (count($parts) < 3) {
            return FormConfig::make();
        }

        $locale = $parts[0];
        $integration = $parts[1];
        $form = pathinfo($parts[2], PATHINFO_FILENAME);

        return FormConfig::make()
            ->locale($locale)
            ->integration($integration)
            ->form($form)
            ->data(collect($data));
    }

    public function getItemKey($item): string
    {
        return $item->id();
    }
}
