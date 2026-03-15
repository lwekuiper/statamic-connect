<?php

namespace Lwekuiper\StatamicConnect\Data;

use Statamic\Contracts\Data\Localization;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\HasOrigin;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class FormConfig implements Localization
{
    use ContainsData;
    use ExistsAsFile;
    use FluentlyGetsAndSets;
    use HasOrigin;

    protected ?string $integration = null;

    protected ?string $form = null;

    protected ?string $locale = null;

    public static function make(): static
    {
        return new static;
    }

    public function id(): string
    {
        return "{$this->locale()}::{$this->integration()}::{$this->form()}";
    }

    public function integration(?string $integration = null): mixed
    {
        return $this->fluentlyGetOrSet('integration')->args(func_get_args());
    }

    public function form(?string $form = null): mixed
    {
        return $this->fluentlyGetOrSet('form')->args(func_get_args());
    }

    public function locale(?string $locale = null): mixed
    {
        return $this->fluentlyGetOrSet('locale')->args(func_get_args());
    }

    public function site(): \Statamic\Sites\Site
    {
        return Site::get($this->locale());
    }

    public function path(): string
    {
        return Stache::store('connect-form-configs')->directory()
            .$this->locale().'/'
            .$this->integration().'/'
            .$this->form().'.yaml';
    }

    public function emailField(): ?string
    {
        return $this->get('email_field');
    }

    public function consentField(): ?string
    {
        return $this->get('consent_field');
    }

    public function listIds(): array
    {
        return $this->get('list_ids', []);
    }

    public function tagIds(): array
    {
        return $this->get('tag_ids', []);
    }

    public function mergeFields(): array
    {
        return $this->get('merge_fields', []);
    }

    public function isEnabled(): bool
    {
        return (bool) $this->get('enabled', true);
    }

    public function fileData(): array
    {
        return $this->data()->all();
    }

    public function reference(): string
    {
        return "connect-form-config::{$this->id()}";
    }

    public function getOriginByString(string $origin): ?static
    {
        $parts = explode('::', $origin);

        if (count($parts) !== 3) {
            return null;
        }

        [$locale, $integration, $form] = $parts;

        return app(FormConfigRepository::class)->find($form, $integration, $locale);
    }
}
