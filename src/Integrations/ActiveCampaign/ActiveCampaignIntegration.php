<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicConnect\Integrations\ActiveCampaign;

use Lwekuiper\StatamicConnect\Connectors\ActiveCampaignConnector;
use Lwekuiper\StatamicConnect\Data\FormConfig;
use Lwekuiper\StatamicConnect\Integrations\BaseIntegration;
use Statamic\Forms\Submission;
use Statamic\Support\Arr;

class ActiveCampaignIntegration extends BaseIntegration
{
    public function name(): string
    {
        return 'activecampaign';
    }

    public function subscribe(string $email, array $data, FormConfig $formConfig): bool
    {
        $connector = app(ActiveCampaignConnector::class);

        $contact = $connector->syncContact($email, $data);

        if (! $contact) {
            return false;
        }

        $contactId = Arr::get($contact, 'contact.id');

        foreach ($formConfig->listIds() as $listId) {
            $connector->updateListStatus($contactId, $listId);
        }

        foreach ($formConfig->tagIds() as $tagId) {
            $connector->addTagToContact($contactId, $tagId);
        }

        return true;
    }

    public function mapFields(Submission $submission, FormConfig $formConfig): array
    {
        $data = collect($submission->data());
        $mergeFields = $formConfig->mergeFields();

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

    public function validateConfig(): bool
    {
        return ! empty($this->configValue('api_url'))
            && ! empty($this->configValue('api_key'));
    }
}
