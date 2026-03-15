<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicConnect\Listeners;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Lwekuiper\StatamicConnect\Connectors\ActiveCampaignConnector;
use Lwekuiper\StatamicConnect\Connectors\HubSpotConnector;
use Lwekuiper\StatamicConnect\Facades\FormConfig;
use Statamic\Events\SubmissionCreated;
use Statamic\Facades\Addon;
use Statamic\Facades\Site;
use Statamic\Forms\Submission;

class DispatchToIntegrations
{
    public function handle(SubmissionCreated $event): void
    {
        $submission = $event->submission;
        $site = $this->resolveSite();

        $this->handleActiveCampaign($submission, $site);
        $this->handleHubSpot($submission, $site);
    }

    private function handleActiveCampaign(Submission $submission, string $site): void
    {
        $resolved = FormConfig::findResolved('activecampaign', $submission->form()->handle(), $site);

        if (! $resolved) {
            return;
        }

        if ($resolved->values()->isEmpty()) {
            return;
        }

        $data = collect($submission->data());

        if (! $this->hasConsent($data, $resolved->value('consent_field'))) {
            return;
        }

        $email = $data->get($resolved->value('email_field') ?? 'email');
        $mergeData = $this->getActiveCampaignMergeData($data, $resolved->value('merge_fields') ?? []);

        $connector = app(ActiveCampaignConnector::class);
        $contact = $connector->syncContact($email, $mergeData);

        if (! $contact) {
            return;
        }

        $contactId = Arr::get($contact, 'contact.id');

        foreach ($resolved->value('list_ids') ?? [] as $listId) {
            $connector->updateListStatus($contactId, $listId);
        }

        foreach ($resolved->value('tag_ids') ?? [] as $tagId) {
            $connector->addTagToContact($contactId, $tagId);
        }
    }

    private function handleHubSpot(Submission $submission, string $site): void
    {
        $formConfig = FormConfig::find('hubspot', $submission->form()->handle(), $site);

        if (! $formConfig) {
            return;
        }

        $data = collect($submission->data());
        $configData = $formConfig->data();

        if (! $this->hasConsent($data, $configData->get('consent_field'))) {
            return;
        }

        $email = $data->get($configData->get('email_field', 'email'));
        $contactData = $this->getHubSpotContactData($data, $configData->get('contact_properties', []));

        app(HubSpotConnector::class)->createOrUpdateContact($email, $contactData);
    }

    private function hasConsent(Collection $data, ?string $consentField): bool
    {
        if (! $consentField) {
            return true;
        }

        return filter_var(
            Arr::get(Arr::wrap($data->get($consentField, false)), 0, false),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    private function getActiveCampaignMergeData(Collection $data, array $mergeFields): array
    {
        [$standardFields, $customFields] = collect($mergeFields)->partition(function ($item) {
            return in_array($item['activecampaign_field'], ['email', 'firstName', 'lastName', 'phone']);
        });

        $standardData = $standardFields->mapWithKeys(function ($item) use ($data) {
            return [$item['activecampaign_field'] => $data->get($item['statamic_field'])];
        })->filter()->all();

        $customData = $customFields->map(function ($item) use ($data) {
            $fieldValue = $data->get($item['statamic_field']);

            if (is_array($fieldValue)) {
                $fieldValue = implode(', ', array_filter($fieldValue));
            }

            return [
                'field' => $item['activecampaign_field'],
                'value' => (string) $fieldValue,
            ];
        })->filter(fn ($item) => $item['value'] !== '')->values()->all();

        return array_merge($standardData, ['fieldValues' => $customData]);
    }

    private function getHubSpotContactData(Collection $data, array $contactProperties): array
    {
        $result = [];

        foreach ($contactProperties as $mapping) {
            $statamicField = $mapping['statamic_field'];
            $hubspotField = $mapping['hubspot_field'];

            $value = $data->get($statamicField);

            if (is_array($value)) {
                $value = implode(', ', array_filter($value));
            }

            if ($value !== null && $value !== '') {
                $result[$hubspotField] = (string) $value;
            }
        }

        return $result;
    }

    private function resolveSite(): string
    {
        $edition = Addon::get('lwekuiper/statamic-connect')->edition();

        if ($edition === 'pro') {
            $site = Site::findByUrl(URL::previous()) ?? Site::default();

            return $site->handle();
        }

        return Site::default()->handle();
    }
}
