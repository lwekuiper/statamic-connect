<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicConnect\Integrations\Salesforce;

use Lwekuiper\StatamicConnect\Data\FormConfig;
use Lwekuiper\StatamicConnect\Integrations\BaseIntegration;
use Statamic\Forms\Submission;

class SalesforceIntegration extends BaseIntegration
{
    public function name(): string
    {
        return 'salesforce';
    }

    public function subscribe(string $email, array $data, FormConfig $formConfig): bool
    {
        // TODO: Implement Salesforce subscription
        return false;
    }

    public function mapFields(Submission $submission, FormConfig $formConfig): array
    {
        // TODO: Implement Salesforce field mapping
        return [];
    }

    public function validateConfig(): bool
    {
        return ! empty($this->configValue('instance_url'))
            && ! empty($this->configValue('client_id'))
            && ! empty($this->configValue('client_secret'));
    }
}
