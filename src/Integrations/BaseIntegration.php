<?php

namespace Lwekuiper\StatamicConnect\Integrations;

use Lwekuiper\StatamicConnect\Data\FormConfig;
use Statamic\Facades\Site;
use Statamic\Forms\Submission;

abstract class BaseIntegration
{
    abstract public function name(): string;

    abstract public function subscribe(string $email, array $data, FormConfig $formConfig): bool;

    abstract public function mapFields(Submission $submission, FormConfig $formConfig): array;

    abstract public function validateConfig(): bool;

    protected function isProEdition(): bool
    {
        return $this->addon()->edition() === 'pro';
    }

    protected function addon(): \Statamic\Extend\Addon
    {
        return \Statamic\Facades\Addon::get('lwekuiper/statamic-connect');
    }

    public function getSite(): string
    {
        if ($this->isProEdition()) {
            return Site::current()->handle();
        }

        return Site::default()->handle();
    }

    public function hasConsent(Submission $submission, ?string $consentField): bool
    {
        if (! $consentField) {
            return true;
        }

        return (bool) ($submission->data()[$consentField] ?? false);
    }

    protected function resolveEmail(Submission $submission, FormConfig $formConfig): ?string
    {
        return $submission->data()[$formConfig->emailField()] ?? null;
    }

    protected function configValue(string $key, mixed $default = null): mixed
    {
        return config("statamic.connect.integrations.{$this->name()}.{$key}", $default);
    }
}
