<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicConnect\Integrations\Brevo;

use Lwekuiper\StatamicConnect\Data\FormConfig;
use Lwekuiper\StatamicConnect\Integrations\BaseIntegration;
use Statamic\Forms\Submission;

class BrevoIntegration extends BaseIntegration
{
    public function name(): string
    {
        return 'brevo';
    }

    public function subscribe(string $email, array $data, FormConfig $formConfig): bool
    {
        // TODO: Implement Brevo subscription
        return false;
    }

    public function mapFields(Submission $submission, FormConfig $formConfig): array
    {
        // TODO: Implement Brevo field mapping
        return [];
    }

    public function validateConfig(): bool
    {
        return ! empty($this->configValue('api_key'));
    }
}
