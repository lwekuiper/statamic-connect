<?php

namespace Lwekuiper\StatamicConnect\Tests\Stache;

use Lwekuiper\StatamicConnect\Data\FormConfig;
use Lwekuiper\StatamicConnect\Data\FormConfigCollection;
use Lwekuiper\StatamicConnect\Exceptions\FormConfigNotFoundException;
use Lwekuiper\StatamicConnect\Facades\FormConfig as FormConfigFacade;
use Lwekuiper\StatamicConnect\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Site;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class FormConfigRepositoryTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_can_make_a_form_config()
    {
        $formConfig = FormConfigFacade::make();

        $this->assertInstanceOf(FormConfig::class, $formConfig);
    }

    #[Test]
    public function it_can_save_and_find_a_form_config()
    {
        $site = Site::default()->handle();

        $formConfig = FormConfigFacade::make()
            ->integration('activecampaign')
            ->form('test_form')
            ->locale($site)
            ->data(['email_field' => 'email']);

        FormConfigFacade::save($formConfig);

        $found = FormConfigFacade::find('activecampaign', 'test_form', $site);

        $this->assertNotNull($found);
        $this->assertEquals('email', $found->emailField());
    }

    #[Test]
    public function it_throws_when_form_config_not_found()
    {
        $this->expectException(FormConfigNotFoundException::class);

        FormConfigFacade::findOrFail('activecampaign', 'nonexistent', 'en');
    }

    #[Test]
    public function it_returns_form_config_collection_when_filtering_by_integration()
    {
        $result = FormConfigFacade::whereIntegration('activecampaign');

        $this->assertInstanceOf(FormConfigCollection::class, $result);
    }
}
