<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicConnect\Http\Controllers\Cp;

use Illuminate\Http\Request;
use Lwekuiper\StatamicConnect\Facades\AddonConfig;
use Lwekuiper\StatamicConnect\Facades\FormConfig;
use Statamic\CP\PublishForm;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Http\Controllers\CP\CpController;

class AddonConfigController extends CpController
{
    public function edit()
    {
        $user = User::current();
        abort_unless($user->isSuper() || $user->hasPermission('configure forms'), 401);

        $values = [
            'sites' => Site::all()->map(fn ($site) => [
                'name' => $site->name(),
                'handle' => $site->handle(),
                'enabled' => AddonConfig::isEnabled($site->handle()),
                'origin' => AddonConfig::originFor($site->handle()),
            ])->values()->all(),
        ];

        return PublishForm::make($this->blueprint())
            ->title(__('Configure Connect'))
            ->values($values)
            ->asConfig()
            ->submittingTo(cp_route('connect.settings.update'));
    }

    public function update(Request $request)
    {
        $user = User::current();
        abort_unless($user->isSuper() || $user->hasPermission('configure forms'), 401);

        $fields = $this->blueprint()->fields()->addValues($request->all());
        $fields->validate();
        $values = $fields->process()->values()->all();

        $previousSites = AddonConfig::sites()->keys();

        $newSites = collect($values['sites'])
            ->filter(fn ($site) => $site['enabled'])
            ->mapWithKeys(fn ($site) => [$site['handle'] => $site['origin']]);

        AddonConfig::save($newSites);

        $this->syncFormConfigs($previousSites->all(), $newSites->keys()->all());

        return response('', 204);
    }

    private function syncFormConfigs(array $previousSites, array $newSites): void
    {
        $addedSites = array_diff($newSites, $previousSites);
        $removedSites = array_diff($previousSites, $newSites);

        $integrations = array_keys(config('statamic.connect.integrations', []));

        if (! empty($addedSites)) {
            $existingHandles = collect($previousSites)
                ->flatMap(function ($site) use ($integrations) {
                    $handles = collect();
                    foreach ($integrations as $integration) {
                        $handles = $handles->merge(
                            FormConfig::whereLocale($integration, $site)->map->handle()
                        );
                    }

                    return $handles;
                })
                ->unique()
                ->all();

            foreach ($addedSites as $site) {
                foreach ($integrations as $integration) {
                    foreach ($existingHandles as $handle) {
                        if (! FormConfig::find($integration, $handle, $site)) {
                            FormConfig::make()->integration($integration)->form($handle)->locale($site)->save();
                        }
                    }
                }
            }
        }

        foreach ($removedSites as $site) {
            foreach ($integrations as $integration) {
                FormConfig::whereLocale($integration, $site)->each->delete();
            }
        }
    }

    protected function blueprint()
    {
        return Blueprint::make()->setContents([
            'tabs' => [
                'main' => [
                    'sections' => [
                        [
                            'display' => __('Sites'),
                            'fields' => [
                                [
                                    'handle' => 'sites',
                                    'field' => [
                                        'type' => 'connect_sites',
                                        'required' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
