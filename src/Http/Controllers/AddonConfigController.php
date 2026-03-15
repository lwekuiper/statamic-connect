<?php

namespace Lwekuiper\StatamicConnect\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Lwekuiper\StatamicConnect\Data\AddonConfig;
use Lwekuiper\StatamicConnect\IntegrationManager;

class AddonConfigController extends Controller
{
    public function edit(IntegrationManager $manager, AddonConfig $config)
    {
        return view('connect::settings', [
            'integrations' => $manager->all()->map(fn ($integration) => [
                'name' => $integration->name(),
                'label' => $integration->label(),
            ]),
            'config' => $config->toArray(),
        ]);
    }

    public function update(Request $request, AddonConfig $config)
    {
        $validated = $request->validate([
            'sites' => 'array',
            'sites.*.integrations' => 'array',
            'sites.*.integrations.*' => 'string',
        ]);

        foreach ($validated['sites'] ?? [] as $site => $siteConfig) {
            $config->setEnabledIntegrations($site, $siteConfig['integrations'] ?? []);
        }

        $config->save();

        return back()->with('success', 'Settings saved.');
    }
}
