<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicConnect\Integrations\HubSpot;

use Lwekuiper\StatamicConnect\Connectors\HubSpotConnector;
use Lwekuiper\StatamicConnect\Data\FormConfig;
use Lwekuiper\StatamicConnect\Integrations\BaseIntegration;
use Statamic\Forms\Submission;

class HubSpotIntegration extends BaseIntegration
{
    public function name(): string
    {
        return 'hubspot';
    }

    public function subscribe(string $email, array $data, FormConfig $formConfig): bool
    {
        $result = app(HubSpotConnector::class)->createOrUpdateContact($email, $data);

        return $result !== null;
    }

    public function mapFields(Submission $submission, FormConfig $formConfig): array
    {
        $data = collect($submission->data());
        $contactProperties = $formConfig->contactProperties();
        $result = [];

        foreach ($contactProperties as $mapping) {
            $value = $data->get($mapping['statamic_field']);

            if (is_array($value)) {
                $value = implode(', ', array_filter($value));
            }

            if ($value !== null && $value !== '') {
                $result[$mapping['hubspot_field']] = (string) $value;
            }
        }

        return $result;
    }

    public function validateConfig(): bool
    {
        return ! empty($this->configValue('access_token'));
    }
}
