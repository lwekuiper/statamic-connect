<?php

namespace Lwekuiper\StatamicConnect\Integrations\ActiveCampaign;

use Lwekuiper\StatamicConnect\Data\FormConfig;
use Lwekuiper\StatamicConnect\Integrations\BaseConnector;
use Lwekuiper\StatamicConnect\Integrations\BaseIntegration;

class ActiveCampaign extends BaseIntegration
{
    protected const STANDARD_FIELDS = ['email', 'firstName', 'lastName', 'phone'];

    public function name(): string
    {
        return 'activecampaign';
    }

    public function label(): string
    {
        return 'ActiveCampaign';
    }

    public function validateConfig(): bool
    {
        return $this->config('api_url') && $this->config('api_key');
    }

    public function subscribe(string $email, array $mergeData, FormConfig $config): bool
    {
        $result = $this->connector()->syncContact($email, $mergeData);

        return $result !== null;
    }

    public function mapFields(array $formData, FormConfig $config): array
    {
        $mergeFields = $config->mergeFields();
        $standardFields = [];
        $customFields = [];

        foreach ($mergeFields as $mapping) {
            $formField = $mapping['form_field'] ?? null;
            $remoteField = $mapping['remote_field'] ?? null;

            if (! $formField || ! $remoteField || ! isset($formData[$formField])) {
                continue;
            }

            $value = $formData[$formField];

            if (is_array($value)) {
                $value = implode('||', $value);
            }

            if (in_array($remoteField, self::STANDARD_FIELDS)) {
                $standardFields[$remoteField] = $value;
            } else {
                $customFields[] = [
                    'field' => $remoteField,
                    'value' => $value,
                ];
            }
        }

        if (! empty($customFields)) {
            $standardFields['fieldValues'] = $customFields;
        }

        return $standardFields;
    }

    public function getLists(): array
    {
        return $this->connector()->getLists() ?? [];
    }

    public function getRemoteFields(): array
    {
        return $this->connector()->getCustomFields() ?? [];
    }

    public function getTags(): array
    {
        return $this->connector()->getTags() ?? [];
    }

    public function afterSubscribe(string $contactId, FormConfig $config): void
    {
        $connector = $this->connector();

        foreach ($config->listIds() as $listId) {
            $connector->updateListStatus($contactId, $listId);
        }

        foreach ($config->tagIds() as $tagId) {
            $connector->addTagToContact($contactId, $tagId);
        }
    }

    protected function connector(): ActiveCampaignConnector
    {
        return app(ActiveCampaignConnector::class);
    }

    public function configFieldItems(): array
    {
        return array_merge(parent::configFieldItems(), [
            'tag_ids' => [
                'type' => 'connect_remote_tag',
                'display' => 'Tags',
                'instructions' => 'Select tags to apply to contacts.',
                'integration' => $this->name(),
            ],
        ]);
    }
}
