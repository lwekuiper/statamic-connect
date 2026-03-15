<?php

namespace Lwekuiper\StatamicConnect\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Lwekuiper\StatamicConnect\Data\FormConfig;
use Lwekuiper\StatamicConnect\IntegrationManager;
use Lwekuiper\StatamicConnect\Stache\FormConfigRepository;
use Statamic\Facades\Site;

class FormConfigController extends Controller
{
    public function __construct(
        protected IntegrationManager $manager,
        protected FormConfigRepository $repository,
    ) {}

    public function edit(string $integration, string $form)
    {
        $integrationInstance = $this->manager->find($integration);

        if (! $integrationInstance) {
            abort(404);
        }

        $site = $integrationInstance->isProEdition()
            ? request()->get('site', Site::default()->handle())
            : Site::default()->handle();

        $config = $this->repository->findResolved($form, $integration, $site);

        return view('connect::form-config', [
            'integration' => $integrationInstance,
            'form' => $form,
            'site' => $site,
            'config' => $config,
            'fieldItems' => $integrationInstance->configFieldItems(),
        ]);
    }

    public function update(Request $request, string $integration, string $form)
    {
        $integrationInstance = $this->manager->find($integration);

        if (! $integrationInstance) {
            abort(404);
        }

        $site = $integrationInstance->isProEdition()
            ? $request->get('site', Site::default()->handle())
            : Site::default()->handle();

        $config = $this->repository->find($form, $integration, $site);

        if (! $config) {
            $config = FormConfig::make()
                ->locale($site)
                ->integration($integration)
                ->form($form);

            $defaultSite = Site::default()->handle();
            if ($site !== $defaultSite) {
                $origin = $this->repository->find($form, $integration, $defaultSite);
                if ($origin) {
                    $config->origin($origin);
                }
            }
        }

        $config->data(collect($request->except(['_token', '_method', 'site'])));

        $this->repository->save($config);

        return back()->with('success', 'Form configuration saved.');
    }

    public function destroy(string $integration, string $form)
    {
        $site = request()->get('site', Site::default()->handle());
        $config = $this->repository->find($form, $integration, $site);

        if ($config) {
            $this->repository->delete($config);
        }

        return back()->with('success', 'Form configuration deleted.');
    }
}
