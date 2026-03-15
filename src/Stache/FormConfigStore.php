<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicConnect\Stache;

use Lwekuiper\StatamicConnect\Facades\FormConfig;
use Statamic\Facades\Path;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;
use Statamic\Stache\Stores\BasicStore;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class FormConfigStore extends BasicStore
{
    protected $storeIndexes = [
        'integration',
        'handle',
        'locale',
    ];

    protected string $integration;

    public function __construct(string $integration)
    {
        $this->integration = $integration;
    }

    public function key()
    {
        return $this->integration.'-form-configs';
    }

    public function getItemFilter(SplFileInfo $file)
    {
        if ($file->getExtension() !== 'yaml') {
            return false;
        }

        $filename = Str::after(Path::tidy($file->getPathName()), $this->directory);

        if ($filename === 'config.yaml' || Str::endsWith($filename, '/config.yaml')) {
            return false;
        }

        $slashes = substr_count($filename, '/');

        return $slashes === 0 || $slashes === 1;
    }

    public function makeItemFromFile($path, $contents)
    {
        $relative = Str::after($path, $this->directory);
        $handle = Str::before($relative, '.yaml');

        $data = YAML::file($path)->parse($contents);

        // Migrate legacy singular keys to plural (ActiveCampaign)
        if (! isset($data['list_ids']) && isset($data['list_id'])) {
            $data['list_ids'] = Arr::wrap($data['list_id']);
        }
        unset($data['list_id']);

        if (! isset($data['tag_ids']) && isset($data['tag_id'])) {
            $data['tag_ids'] = Arr::wrap($data['tag_id']);
        }
        unset($data['tag_id']);

        $formConfig = FormConfig::make()
            ->integration($this->integration)
            ->initialPath($path)
            ->data($data);

        $handle = explode('/', $handle);
        if (count($handle) > 1) {
            $formConfig->form($handle[1])
                ->locale($handle[0]);
        } else {
            $formConfig->form($handle[0])
                ->locale(Site::default()->handle());
        }

        return $formConfig;
    }

    public function getItemKey($item)
    {
        return "{$item->integration()}::{$item->handle()}::{$item->locale()}";
    }
}
