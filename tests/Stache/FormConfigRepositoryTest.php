<?php

namespace Lwekuiper\StatamicConnect\Tests\Stache;

use Lwekuiper\StatamicConnect\Data\FormConfig;
use Lwekuiper\StatamicConnect\Stache\FormConfigRepository;
use Lwekuiper\StatamicConnect\Tests\TestCase;

class FormConfigRepositoryTest extends TestCase
{
    protected FormConfigRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(FormConfigRepository::class);
    }

    public function test_find_returns_null_when_not_found(): void
    {
        $this->assertNull($this->repository->find('nonexistent', 'activecampaign'));
    }

    public function test_for_form_returns_empty_collection_when_no_configs(): void
    {
        $this->assertTrue($this->repository->forForm('nonexistent')->isEmpty());
    }

    public function test_for_integration_returns_empty_collection_when_no_configs(): void
    {
        $this->assertTrue($this->repository->forIntegration('nonexistent')->isEmpty());
    }

    public function test_form_config_has_correct_id(): void
    {
        $config = FormConfig::make()
            ->locale('default')
            ->integration('activecampaign')
            ->form('contact');

        $this->assertEquals('default::activecampaign::contact', $config->id());
    }

    public function test_form_config_accessors(): void
    {
        $config = FormConfig::make()
            ->locale('default')
            ->integration('activecampaign')
            ->form('contact')
            ->data(collect([
                'email_field' => 'email',
                'consent_field' => 'gdpr',
                'list_ids' => ['1', '2'],
                'tag_ids' => ['10'],
                'merge_fields' => [
                    ['form_field' => 'name', 'remote_field' => 'firstName'],
                ],
            ]));

        $this->assertEquals('email', $config->emailField());
        $this->assertEquals('gdpr', $config->consentField());
        $this->assertEquals(['1', '2'], $config->listIds());
        $this->assertEquals(['10'], $config->tagIds());
        $this->assertCount(1, $config->mergeFields());
    }

    public function test_form_config_defaults(): void
    {
        $config = FormConfig::make()
            ->locale('default')
            ->integration('activecampaign')
            ->form('contact')
            ->data(collect([]));

        $this->assertNull($config->emailField());
        $this->assertNull($config->consentField());
        $this->assertEquals([], $config->listIds());
        $this->assertEquals([], $config->tagIds());
        $this->assertEquals([], $config->mergeFields());
        $this->assertTrue($config->isEnabled());
    }
}
