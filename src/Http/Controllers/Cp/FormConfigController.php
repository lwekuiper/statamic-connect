<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicConnect\Http\Controllers\Cp;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Lwekuiper\StatamicConnect\Facades\AddonConfig;
use Lwekuiper\StatamicConnect\Facades\FormConfig;
use Statamic\Facades\Addon;
use Statamic\Facades\Blueprint;
use Statamic\Facades\Form as FormFacade;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint as BlueprintContract;
use Statamic\Forms\Form;
use Statamic\Http\Controllers\CP\CpController;

class FormConfigController extends CpController
{
    public function index(Request $request, string $integration)
    {
        $user = User::current();
        abort_unless($user->isSuper() || $user->hasPermission('configure forms'), 401);

        [$site, $edition] = $this->getAddonContext($request);

        $urlParams = $edition === 'pro' ? ['site' => $site] : [];

        $forms = FormFacade::all();

        $formConfigs = $forms->map(function ($form) use ($integration, $urlParams, $site) {
            $localConfig = FormConfig::find($integration, $form->handle(), $site);
            $resolved = FormConfig::findResolved($integration, $form->handle(), $site);

            $resolvedValues = $resolved?->values() ?? collect();
            $hasLocalData = $localConfig !== null && ! $localConfig->data()->isEmpty();
            $hasValues = $resolvedValues->filter()->isNotEmpty();

            $row = [
                'title' => $form->title(),
                'edit_url' => cp_route("connect.{$integration}.form-config.edit", ['form' => $form->handle(), ...$urlParams]),
                'delete_url' => $hasLocalData ? cp_route("connect.{$integration}.form-config.destroy", ['form' => $form->handle(), ...$urlParams]) : null,
                'status' => $hasValues ? 'published' : 'draft',
            ];

            if ($integration === 'activecampaign') {
                $row['lists'] = count($resolvedValues->get('list_ids', []));
                $row['tags'] = count($resolvedValues->get('tag_ids', []));
            }

            return $row;
        })->values();

        $viewData = [
            'formConfigs' => $formConfigs,
            'integration' => $integration,
        ];

        if ($edition === 'pro') {
            $viewData = array_merge($viewData, [
                'locale' => $site,
                'localizations' => $this->getEnabledSites()->map(fn ($localization) => [
                    'handle' => $localization->handle(),
                    'name' => $localization->name(),
                    'active' => $localization->handle() === $site,
                    'url' => cp_route("connect.{$integration}.index", ['site' => $localization->handle()]),
                ])->values()->all(),
            ]);
        }

        if ($request->wantsJson()) {
            return $viewData;
        }

        if ($forms->isEmpty()) {
            return Inertia::render('connect::Empty', [
                'createUrl' => cp_route('forms.create'),
                'integration' => $integration,
            ]);
        }

        return Inertia::render('connect::Index', [
            'createFormUrl' => cp_route('forms.create'),
            'configureUrl' => cp_route('connect.settings.edit'),
            'integration' => $integration,
            'formConfigs' => $viewData['formConfigs'],
            'localizations' => $viewData['localizations'] ?? [],
            'site' => $viewData['locale'] ?? '',
        ]);
    }

    public function edit(Request $request, string $integration, Form $form)
    {
        [$site, $edition] = $this->getAddonContext($request);

        $blueprint = $this->getBlueprint($integration);
        $formConfig = FormConfig::find($integration, $form->handle(), $site);

        $hasOrigin = $edition === 'pro' && $formConfig && $formConfig->hasOrigin();

        if ($hasOrigin) {
            $originValues = $formConfig->origin()->values()->all();
            $displayValues = $formConfig->values()->all();

            $fields = $blueprint->fields()->addValues($displayValues)->preProcess();

            [$originValues, $originMeta] = $this->extractFromFields($originValues, $blueprint);
            $localizedFields = $formConfig->data()->keys()->all();
        } else {
            $fields = $blueprint->fields();

            if ($formConfig) {
                $fields = $fields->addValues($formConfig->data()->all());
            }

            $fields = $fields->preProcess();
        }

        $viewData = [
            'title' => $form->title(),
            'action' => cp_route("connect.{$integration}.form-config.update", ['form' => $form->handle(), 'site' => $site]),
            'deleteUrl' => $formConfig?->deleteUrl(),
            'listingUrl' => cp_route("connect.{$integration}.index", ['site' => $site]),
            'blueprint' => $blueprint->toPublishArray(),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'hasOrigin' => $hasOrigin,
            'originValues' => $originValues ?? null,
            'originMeta' => $originMeta ?? null,
            'localizedFields' => $localizedFields ?? [],
            'integration' => $integration,
        ];

        if ($edition === 'pro') {
            $viewData = array_merge($viewData, [
                'locale' => $site,
                'localizations' => $this->getEnabledSites()->map(fn ($localization) => [
                    'handle' => $localization->handle(),
                    'name' => $localization->name(),
                    'active' => $localization->handle() === $site,
                    'origin' => ! AddonConfig::hasOrigin($localization->handle()),
                    'url' => cp_route("connect.{$integration}.form-config.edit", ['form' => $form->handle(), 'site' => $localization->handle()]),
                ])->values()->all(),
                'configureUrl' => cp_route('connect.settings.edit'),
            ]);
        }

        if ($request->wantsJson()) {
            return $viewData;
        }

        return Inertia::render('connect::Edit', [
            'title' => $viewData['title'],
            'action' => $viewData['action'],
            'deleteUrl' => $viewData['deleteUrl'],
            'listingUrl' => $viewData['listingUrl'],
            'blueprint' => $viewData['blueprint'],
            'values' => $viewData['values'],
            'meta' => $viewData['meta'],
            'localizations' => $viewData['localizations'] ?? [],
            'site' => $viewData['locale'] ?? '',
            'hasOrigin' => $viewData['hasOrigin'],
            'originValues' => $viewData['originValues'],
            'originMeta' => $viewData['originMeta'],
            'localizedFields' => $viewData['localizedFields'],
            'configureUrl' => $viewData['configureUrl'] ?? null,
            'integration' => $integration,
        ]);
    }

    public function update(Request $request, string $integration, Form $form)
    {
        [$site, $edition] = $this->getAddonContext($request);

        $blueprint = $this->getBlueprint($integration);
        $fields = $blueprint->fields()->addValues($request->all());
        $fields->validate();

        $values = $fields->process()->values();

        $hasOrigin = $edition === 'pro' && AddonConfig::hasOrigin($site);

        if ($hasOrigin) {
            $values = $values->only($request->input('_localized', []));
        }

        $values = $values->all();

        if (! $formConfig = FormConfig::find($integration, $form->handle(), $site)) {
            $formConfig = FormConfig::make()->integration($integration)->form($form)->locale($site);
        }

        $formConfig->data($values);

        $formConfig->save();

        if ($edition === 'pro') {
            FormConfig::ensureLocalizationsExist($integration, $form->handle());
        }

        return response()->json(['message' => __('Configuration saved')]);
    }

    public function destroy(Request $request, string $integration, Form $form)
    {
        [$site] = $this->getAddonContext($request);

        if (! $formConfig = FormConfig::find($integration, $form->handle(), $site)) {
            return $this->pageNotFound();
        }

        if ($formConfig->hasOrigin()) {
            $formConfig->data(collect())->save();
        } else {
            $formConfig->delete();
        }

        return response('', 204);
    }

    private function extractFromFields(array $values, BlueprintContract $blueprint): array
    {
        $fields = $blueprint
            ->fields()
            ->addValues($values)
            ->preProcess();

        return [$fields->values()->all(), $fields->meta()->all()];
    }

    private function getAddonContext(Request $request): array
    {
        $edition = Addon::get('lwekuiper/statamic-connect')->edition();

        $site = $edition === 'pro'
            ? $request->site ?? Site::selected()->handle()
            : Site::default()->handle();

        return [$site, $edition];
    }

    private function getEnabledSites(): \Illuminate\Support\Collection
    {
        return Site::all()->filter(fn ($site) => AddonConfig::isEnabled($site->handle()));
    }

    private function getBlueprint(string $integration): BlueprintContract
    {
        return match ($integration) {
            'activecampaign' => $this->getActiveCampaignBlueprint(),
            'hubspot' => $this->getHubSpotBlueprint(),
            default => throw new \InvalidArgumentException("Unknown integration: {$integration}"),
        };
    }

    private function getActiveCampaignBlueprint(): BlueprintContract
    {
        return Blueprint::make()->setContents([
            'tabs' => [
                'general' => [
                    'display' => 'General',
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'email_field',
                                    'field' => [
                                        'display' => 'Email Field',
                                        'instructions' => 'The form field that contains the email of the subscriber.',
                                        'type' => 'statamic_form_fields',
                                        'validate' => 'required',
                                        'width' => 50,
                                        'localizable' => true,
                                    ],
                                ],
                                [
                                    'handle' => 'consent_field',
                                    'field' => [
                                        'display' => 'Consent Field',
                                        'instructions' => 'The form field that contains the consent of the subscriber.',
                                        'type' => 'statamic_form_fields',
                                        'width' => 50,
                                        'localizable' => true,
                                    ],
                                ],
                                [
                                    'handle' => 'list_ids',
                                    'field' => [
                                        'display' => 'Lists',
                                        'instructions' => 'The ActiveCampaign lists you want to add the subscriber to.',
                                        'type' => 'activecampaign_list',
                                        'validate' => 'required',
                                        'width' => 50,
                                        'localizable' => true,
                                    ],
                                ],
                                [
                                    'handle' => 'tag_ids',
                                    'field' => [
                                        'display' => 'Tags',
                                        'instructions' => 'The ActiveCampaign tags you want to add to the subscriber.',
                                        'type' => 'activecampaign_tag',
                                        'width' => 50,
                                        'localizable' => true,
                                    ],
                                ],
                                [
                                    'handle' => 'merge_fields',
                                    'field' => [
                                        'display' => 'Merge Fields',
                                        'instructions' => 'Add the form fields you want to map to ActiveCampaign fields.',
                                        'type' => 'grid',
                                        'mode' => 'table',
                                        'listable' => 'hidden',
                                        'fullscreen' => false,
                                        'width' => 100,
                                        'add_row' => 'Add Merge Field',
                                        'localizable' => true,
                                        'fields' => [
                                            [
                                                'handle' => 'statamic_field',
                                                'field' => [
                                                    'display' => 'Form Field',
                                                    'type' => 'statamic_form_fields',
                                                ],
                                            ],
                                            [
                                                'handle' => 'activecampaign_field',
                                                'field' => [
                                                    'display' => 'Merge Field',
                                                    'type' => 'activecampaign_merge_fields',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    private function getHubSpotBlueprint(): BlueprintContract
    {
        return Blueprint::make()->setContents([
            'tabs' => [
                'general' => [
                    'display' => 'General',
                    'sections' => [
                        [
                            'fields' => [
                                [
                                    'handle' => 'email_field',
                                    'field' => [
                                        'display' => 'Email Field',
                                        'instructions' => 'The form field that contains the email of the subscriber.',
                                        'type' => 'statamic_form_fields',
                                        'validate' => 'required',
                                        'width' => 50,
                                    ],
                                ],
                                [
                                    'handle' => 'consent_field',
                                    'field' => [
                                        'display' => 'Consent Field',
                                        'instructions' => 'The form field that contains the consent of the subscriber.',
                                        'type' => 'statamic_form_fields',
                                        'width' => 50,
                                    ],
                                ],
                                [
                                    'handle' => 'contact_properties',
                                    'field' => [
                                        'display' => 'Contact Properties',
                                        'instructions' => 'Add the form fields you want to map to HubSpot contact properties.',
                                        'type' => 'grid',
                                        'mode' => 'table',
                                        'listable' => 'hidden',
                                        'fullscreen' => false,
                                        'add_row' => 'Add Contact Property',
                                        'fields' => [
                                            [
                                                'handle' => 'statamic_field',
                                                'field' => [
                                                    'display' => 'Form Field',
                                                    'type' => 'statamic_form_fields',
                                                ],
                                            ],
                                            [
                                                'handle' => 'hubspot_field',
                                                'field' => [
                                                    'display' => 'HubSpot Property',
                                                    'type' => 'hubspot_contact_properties',
                                                ],
                                            ],
                                        ],
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
