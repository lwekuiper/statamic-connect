<?php

declare(strict_types=1);

namespace Lwekuiper\StatamicConnect\Integrations\Klaviyo;

use Lwekuiper\StatamicConnect\Data\FormConfig;
use Lwekuiper\StatamicConnect\Integrations\BaseIntegration;
use Statamic\Forms\Submission;

class KlaviyoIntegration extends BaseIntegration
{
    public function name(): string
    {
        return 'klaviyo';
    }

    public function subscribe(string $email, array $data, FormConfig $formConfig): bool
    {
        // TODO: Implement Klaviyo subscription
        return false;
    }

    public function mapFields(Submission $submission, FormConfig $formConfig): array
    {
        // TODO: Implement Klaviyo field mapping
        return [];
    }

    public function validateConfig(): bool
    {
        return ! empty($this->configValue('api_key'));
    }
}
