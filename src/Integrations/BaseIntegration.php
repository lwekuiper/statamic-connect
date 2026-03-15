<?php

namespace Lwekuiper\StatamicConnect\Integrations;

use Illuminate\Support\Collection;
use Lwekuiper\StatamicConnect\Data\FormConfig;
use Statamic\Facades\Addon;
use Statamic\Facades\Site;
use Statamic\Facades\URL;

abstract class BaseIntegration
{
    abstract public function name(): string;

    abstract public function label(): string;

    abstract public function subscribe(string $email, array $mergeData, FormConfig $config): bool;

    abstract public function mapFields(array $formData, FormConfig $config): array;

    abstract public function validateConfig(): bool;

    abstract public function getLists(): array;

    abstract public function getRemoteFields(): array;

    abstract protected function connector(): BaseConnector;

    public function handle(): string
    {
        return $this->name();
    }

    public function configKey(string $key): string
    {
        return "statamic.connect.integrations.{$this->name()}.{$key}";
    }

    public function config(string $key, mixed $default = null): mixed
    {
        return config($this->configKey($key), $default);
    }

    public function isConfigured(): bool
    {
        return $this->validateConfig();
    }

    public function isProEdition(): bool
    {
        return Addon::get('lwekuiper/statamic-connect')?->edition() === 'pro';
    }

    public function resolveSite(): \Statamic\Sites\Site
    {
        if ($this->isProEdition()) {
            return Site::findByUrl(URL::previous()) ?? Site::default();
        }

        return Site::default();
    }

    public function checkConsent(Collection $formData, FormConfig $config): bool
    {
        $consentField = $config->consentField();

        if (! $consentField) {
            return true;
        }

        return (bool) $formData->get($consentField, false);
    }

    public function getTags(): array
    {
        return [];
    }

    public function afterSubscribe(string $contactId, FormConfig $config): void
    {
        //
    }

    public function configFieldItems(): array
    {
        return [
            'email_field' => [
                'type' => 'connect_form_fields',
                'display' => 'Email Field',
                'instructions' => 'Select the form field that contains the email address.',
            ],
            'consent_field' => [
                'type' => 'connect_form_fields',
                'display' => 'GDPR Consent Field',
                'instructions' => 'Select the form field used for GDPR consent.',
            ],
            'list_ids' => [
                'type' => 'connect_remote_list',
                'display' => 'Lists',
                'instructions' => 'Select the lists to subscribe contacts to.',
                'integration' => $this->name(),
            ],
            'merge_fields' => [
                'type' => 'connect_merge_fields',
                'display' => 'Field Mapping',
                'instructions' => 'Map form fields to integration fields.',
                'integration' => $this->name(),
            ],
        ];
    }
}
