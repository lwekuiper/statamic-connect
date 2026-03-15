<?php

namespace Lwekuiper\StatamicConnect\Tests\Listeners;

use Lwekuiper\StatamicConnect\Facades\FormConfig;
use Lwekuiper\StatamicConnect\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class EnsureFormConfigLocalizationsExistTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_creates_localizations_for_enabled_sites_when_pro()
    {
        $this->setProEdition();

        $this->setSites([
            'en' => ['url' => 'https://example.com/'],
            'nl' => ['url' => 'https://example.com/nl/'],
        ]);

        $form = tap(Form::make('contact'))->save();

        $formConfig = FormConfig::make()
            ->integration('activecampaign')
            ->form('contact')
            ->locale('en')
            ->data(['email_field' => 'email']);
        FormConfig::save($formConfig);

        FormConfig::ensureLocalizationsExist('activecampaign', 'contact');

        $this->assertNotNull(FormConfig::find('activecampaign', 'contact', 'en'));
        $this->assertNotNull(FormConfig::find('activecampaign', 'contact', 'nl'));
    }
}
