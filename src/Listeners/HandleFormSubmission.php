<?php

namespace Lwekuiper\StatamicConnect\Listeners;

use Illuminate\Support\Facades\Log;
use Lwekuiper\StatamicConnect\Data\FormConfig;
use Lwekuiper\StatamicConnect\Integrations\BaseIntegration;
use Lwekuiper\StatamicConnect\IntegrationManager;
use Lwekuiper\StatamicConnect\Stache\FormConfigRepository;
use Statamic\Events\SubmissionCreated;

class HandleFormSubmission
{
    public function __construct(
        protected IntegrationManager $manager,
        protected FormConfigRepository $repository,
    ) {}

    public function handle(SubmissionCreated $event): void
    {
        $submission = $event->submission;
        $formHandle = $submission->form()->handle();
        $formData = collect($submission->data());

        $this->manager->all()->each(function (BaseIntegration $integration) use ($formHandle, $formData) {
            $site = $integration->resolveSite();
            $config = $this->repository->findResolved($formHandle, $integration->name(), $site->handle());

            if (! $config || ! $config->isEnabled()) {
                return;
            }

            if (! $integration->checkConsent($formData, $config)) {
                return;
            }

            $this->processIntegration($integration, $formData->all(), $config);
        });
    }

    protected function processIntegration(BaseIntegration $integration, array $formData, FormConfig $config): void
    {
        try {
            $email = $formData[$config->emailField()] ?? null;

            if (! $email) {
                return;
            }

            $mergeData = $integration->mapFields($formData, $config);
            $success = $integration->subscribe($email, $mergeData, $config);

            if ($success) {
                $integration->afterSubscribe($email, $config);
            }
        } catch (\Throwable $e) {
            Log::error("[Connect] Failed to process {$integration->name()} integration", [
                'error' => $e->getMessage(),
                'form' => $config->form(),
            ]);
        }
    }
}
