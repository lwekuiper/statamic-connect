<?php

namespace Lwekuiper\StatamicConnect\Tests\Unit;

use Illuminate\Support\Collection;
use Lwekuiper\StatamicConnect\Data\FormConfig;
use Lwekuiper\StatamicConnect\Integrations\BaseConnector;
use Lwekuiper\StatamicConnect\Integrations\BaseIntegration;
use Lwekuiper\StatamicConnect\Tests\TestCase;

class StubIntegration extends BaseIntegration
{
    public function name(): string
    {
        return 'stub';
    }

    public function label(): string
    {
        return 'Stub';
    }

    public function subscribe(string $email, array $mergeData, FormConfig $config): bool
    {
        return true;
    }

    public function mapFields(array $formData, FormConfig $config): array
    {
        return $formData;
    }

    public function validateConfig(): bool
    {
        return (bool) $this->config('api_key');
    }

    public function getLists(): array
    {
        return [];
    }

    public function getRemoteFields(): array
    {
        return [];
    }

    protected function connector(): BaseConnector
    {
        return new class extends BaseConnector
        {
            protected function baseUrl(): string
            {
                return 'https://example.com/api';
            }

            protected function headers(): array
            {
                return [];
            }
        };
    }
}

class BaseIntegrationTest extends TestCase
{
    protected StubIntegration $integration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integration = new StubIntegration;
    }

    public function test_config_key_namespacing(): void
    {
        $this->assertEquals(
            'statamic.connect.integrations.stub.api_key',
            $this->integration->configKey('api_key')
        );
    }

    public function test_config_returns_value(): void
    {
        config(['statamic.connect.integrations.stub.api_key' => 'test-key']);

        $this->assertEquals('test-key', $this->integration->config('api_key'));
    }

    public function test_config_returns_default_when_missing(): void
    {
        $this->assertEquals('fallback', $this->integration->config('missing_key', 'fallback'));
    }

    public function test_is_configured_delegates_to_validate_config(): void
    {
        config(['statamic.connect.integrations.stub.api_key' => null]);
        $this->assertFalse($this->integration->isConfigured());

        config(['statamic.connect.integrations.stub.api_key' => 'test-key']);
        $this->assertTrue($this->integration->isConfigured());
    }

    public function test_handle_returns_name(): void
    {
        $this->assertEquals('stub', $this->integration->handle());
    }

    public function test_is_pro_edition_returns_false_by_default(): void
    {
        $this->assertFalse($this->integration->isProEdition());
    }

    public function test_is_pro_edition_returns_true_when_set(): void
    {
        $this->setProEdition();

        $this->assertTrue($this->integration->isProEdition());
    }

    public function test_check_consent_returns_true_when_no_consent_field(): void
    {
        $config = FormConfig::make()
            ->locale('default')
            ->integration('stub')
            ->form('contact')
            ->data(collect([]));

        $this->assertTrue($this->integration->checkConsent(collect(['email' => 'test@example.com']), $config));
    }

    public function test_check_consent_returns_false_when_not_given(): void
    {
        $config = FormConfig::make()
            ->locale('default')
            ->integration('stub')
            ->form('contact')
            ->data(collect(['consent_field' => 'gdpr_consent']));

        $this->assertFalse($this->integration->checkConsent(
            collect(['email' => 'test@example.com', 'gdpr_consent' => false]),
            $config,
        ));
    }

    public function test_check_consent_returns_true_when_given(): void
    {
        $config = FormConfig::make()
            ->locale('default')
            ->integration('stub')
            ->form('contact')
            ->data(collect(['consent_field' => 'gdpr_consent']));

        $this->assertTrue($this->integration->checkConsent(
            collect(['email' => 'test@example.com', 'gdpr_consent' => true]),
            $config,
        ));
    }

    public function test_get_tags_returns_empty_array_by_default(): void
    {
        $this->assertEquals([], $this->integration->getTags());
    }

    public function test_config_field_items_includes_standard_fields(): void
    {
        $items = $this->integration->configFieldItems();

        $this->assertArrayHasKey('email_field', $items);
        $this->assertArrayHasKey('consent_field', $items);
        $this->assertArrayHasKey('list_ids', $items);
        $this->assertArrayHasKey('merge_fields', $items);
    }
}
