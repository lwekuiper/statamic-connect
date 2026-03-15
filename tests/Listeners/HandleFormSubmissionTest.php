<?php

namespace Lwekuiper\StatamicConnect\Tests\Listeners;

use Lwekuiper\StatamicConnect\Data\FormConfig;
use Lwekuiper\StatamicConnect\Integrations\BaseConnector;
use Lwekuiper\StatamicConnect\Integrations\BaseIntegration;
use Lwekuiper\StatamicConnect\IntegrationManager;
use Lwekuiper\StatamicConnect\Listeners\HandleFormSubmission;
use Lwekuiper\StatamicConnect\Stache\FormConfigRepository;
use Lwekuiper\StatamicConnect\Tests\TestCase;
use Mockery;
use Statamic\Events\SubmissionCreated;
use Statamic\Forms\Form;
use Statamic\Forms\Submission;

class HandleFormSubmissionTest extends TestCase
{
    protected IntegrationManager $manager;

    protected FormConfigRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = app(IntegrationManager::class);
        $this->repository = app(FormConfigRepository::class);
    }

    public function test_skips_when_no_form_config_exists(): void
    {
        $integration = Mockery::mock(BaseIntegration::class)->makePartial();
        $integration->shouldReceive('name')->andReturn('test');
        $integration->shouldReceive('resolveSite')->andReturn(\Statamic\Facades\Site::default());
        $integration->shouldNotReceive('subscribe');

        $this->manager->all()->put('test', $integration);

        $listener = new HandleFormSubmission($this->manager, $this->repository);

        $form = Mockery::mock(Form::class);
        $form->shouldReceive('handle')->andReturn('contact');

        $submission = Mockery::mock(Submission::class);
        $submission->shouldReceive('form')->andReturn($form);
        $submission->shouldReceive('data')->andReturn(['email' => 'test@example.com']);

        $listener->handle(new SubmissionCreated($submission));
    }

    public function test_skips_when_consent_not_given(): void
    {
        $config = FormConfig::make()
            ->locale('default')
            ->integration('test')
            ->form('contact')
            ->data(collect([
                'enabled' => true,
                'email_field' => 'email',
                'consent_field' => 'gdpr_consent',
            ]));

        $integration = Mockery::mock(BaseIntegration::class)->makePartial();
        $integration->shouldReceive('name')->andReturn('test');
        $integration->shouldReceive('resolveSite')->andReturn(\Statamic\Facades\Site::default());
        $integration->shouldReceive('checkConsent')->andReturn(false);
        $integration->shouldNotReceive('subscribe');

        $this->manager->all()->put('test', $integration);

        $repository = Mockery::mock(FormConfigRepository::class);
        $repository->shouldReceive('findResolved')->andReturn($config);

        $listener = new HandleFormSubmission($this->manager, $repository);

        $form = Mockery::mock(Form::class);
        $form->shouldReceive('handle')->andReturn('contact');

        $submission = Mockery::mock(Submission::class);
        $submission->shouldReceive('form')->andReturn($form);
        $submission->shouldReceive('data')->andReturn([
            'email' => 'test@example.com',
            'gdpr_consent' => false,
        ]);

        $listener->handle(new SubmissionCreated($submission));
    }
}
