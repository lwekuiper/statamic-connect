<?php

namespace Lwekuiper\StatamicConnect\Tests\Listeners;

use Illuminate\Support\Facades\Http;
use Lwekuiper\StatamicConnect\Facades\FormConfig;
use Lwekuiper\StatamicConnect\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\Site;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class DispatchToIntegrationsTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_does_not_sync_when_no_form_config_exists()
    {
        Http::fake();

        $form = tap(Form::make('contact'))->save();
        $submission = $form->makeSubmission()->data(['email' => 'test@example.com']);

        event(new \Statamic\Events\SubmissionCreated($submission));

        Http::assertNothingSent();
    }

    #[Test]
    public function it_does_not_sync_when_consent_is_not_given()
    {
        Http::fake();

        $form = tap(Form::make('contact'))->save();
        $site = Site::default()->handle();

        $formConfig = FormConfig::make()
            ->integration('activecampaign')
            ->form('contact')
            ->locale($site)
            ->data([
                'email_field' => 'email',
                'consent_field' => 'gdpr_consent',
                'list_ids' => [1],
            ]);
        FormConfig::save($formConfig);

        $submission = $form->makeSubmission()->data([
            'email' => 'test@example.com',
            'gdpr_consent' => false,
        ]);

        event(new \Statamic\Events\SubmissionCreated($submission));

        Http::assertNothingSent();
    }
}
