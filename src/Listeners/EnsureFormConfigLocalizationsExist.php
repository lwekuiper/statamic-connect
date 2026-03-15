<?php

namespace Lwekuiper\StatamicConnect\Listeners;

use Lwekuiper\StatamicConnect\Data\FormConfig;
use Lwekuiper\StatamicConnect\Stache\FormConfigRepository;
use Statamic\Events\FormSaved;
use Statamic\Facades\Site;

class EnsureFormConfigLocalizationsExist
{
    public function __construct(
        protected FormConfigRepository $repository,
    ) {}

    public function handle(FormSaved $event): void
    {
        $formHandle = $event->form->handle();
        $defaultSite = Site::default()->handle();

        $defaultConfigs = $this->repository->forForm($formHandle, $defaultSite);

        if ($defaultConfigs->isEmpty()) {
            return;
        }

        foreach (Site::all() as $site) {
            if ($site->handle() === $defaultSite) {
                continue;
            }

            $defaultConfigs->each(function (FormConfig $originConfig) use ($site, $formHandle) {
                $existing = $this->repository->find($formHandle, $originConfig->integration(), $site->handle());

                if ($existing) {
                    return;
                }

                $localized = FormConfig::make()
                    ->locale($site->handle())
                    ->integration($originConfig->integration())
                    ->form($formHandle)
                    ->origin($originConfig);

                $this->repository->save($localized);
            });
        }
    }
}
