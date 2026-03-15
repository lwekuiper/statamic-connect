<?php

namespace Lwekuiper\StatamicConnect\Tests\Feature\ActiveCampaign;

use Lwekuiper\StatamicConnect\Data\FormConfig;
use Lwekuiper\StatamicConnect\Integrations\ActiveCampaign\ActiveCampaign;
use Lwekuiper\StatamicConnect\Tests\TestCase;

class ActiveCampaignTest extends TestCase
{
    protected ActiveCampaign $integration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integration = new ActiveCampaign;
    }

    public function test_name_returns_activecampaign(): void
    {
        $this->assertEquals('activecampaign', $this->integration->name());
    }

    public function test_label_returns_active_campaign(): void
    {
        $this->assertEquals('ActiveCampaign', $this->integration->label());
    }

    public function test_validate_config_returns_true_when_configured(): void
    {
        config([
            'statamic.connect.integrations.activecampaign.api_url' => 'https://account.api-us1.com',
            'statamic.connect.integrations.activecampaign.api_key' => 'test-key',
        ]);

        $this->assertTrue($this->integration->validateConfig());
    }

    public function test_validate_config_returns_false_when_missing_url(): void
    {
        config([
            'statamic.connect.integrations.activecampaign.api_url' => null,
            'statamic.connect.integrations.activecampaign.api_key' => 'test-key',
        ]);

        $this->assertFalse($this->integration->validateConfig());
    }

    public function test_validate_config_returns_false_when_missing_key(): void
    {
        config([
            'statamic.connect.integrations.activecampaign.api_url' => 'https://account.api-us1.com',
            'statamic.connect.integrations.activecampaign.api_key' => null,
        ]);

        $this->assertFalse($this->integration->validateConfig());
    }

    public function test_map_fields_separates_standard_and_custom_fields(): void
    {
        $config = FormConfig::make()
            ->locale('default')
            ->integration('activecampaign')
            ->form('contact')
            ->data(collect([
                'merge_fields' => [
                    ['form_field' => 'name', 'remote_field' => 'firstName'],
                    ['form_field' => 'phone_number', 'remote_field' => 'phone'],
                    ['form_field' => 'company', 'remote_field' => '1'],
                ],
            ]));

        $result = $this->integration->mapFields([
            'name' => 'John',
            'phone_number' => '+1234567890',
            'company' => 'Acme Inc',
        ], $config);

        $this->assertEquals('John', $result['firstName']);
        $this->assertEquals('+1234567890', $result['phone']);
        $this->assertCount(1, $result['fieldValues']);
        $this->assertEquals('1', $result['fieldValues'][0]['field']);
        $this->assertEquals('Acme Inc', $result['fieldValues'][0]['value']);
    }

    public function test_map_fields_handles_array_values(): void
    {
        $config = FormConfig::make()
            ->locale('default')
            ->integration('activecampaign')
            ->form('contact')
            ->data(collect([
                'merge_fields' => [
                    ['form_field' => 'interests', 'remote_field' => '2'],
                ],
            ]));

        $result = $this->integration->mapFields([
            'interests' => ['coding', 'design'],
        ], $config);

        $this->assertEquals('coding||design', $result['fieldValues'][0]['value']);
    }

    public function test_map_fields_skips_missing_form_data(): void
    {
        $config = FormConfig::make()
            ->locale('default')
            ->integration('activecampaign')
            ->form('contact')
            ->data(collect([
                'merge_fields' => [
                    ['form_field' => 'name', 'remote_field' => 'firstName'],
                    ['form_field' => 'missing_field', 'remote_field' => 'lastName'],
                ],
            ]));

        $result = $this->integration->mapFields([
            'name' => 'John',
        ], $config);

        $this->assertEquals('John', $result['firstName']);
        $this->assertArrayNotHasKey('lastName', $result);
    }

    public function test_config_field_items_includes_tags(): void
    {
        $items = $this->integration->configFieldItems();

        $this->assertArrayHasKey('tag_ids', $items);
        $this->assertEquals('connect_remote_tag', $items['tag_ids']['type']);
    }
}
