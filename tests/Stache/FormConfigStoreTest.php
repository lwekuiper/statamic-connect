<?php

namespace Lwekuiper\StatamicConnect\Tests\Stache;

use Lwekuiper\StatamicConnect\Data\FormConfig;
use Lwekuiper\StatamicConnect\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Path;
use Statamic\Facades\Stache;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class FormConfigStoreTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    private $store;

    public function setUp(): void
    {
        parent::setUp();

        $this->store = Stache::store('activecampaign-form-configs');
    }

    #[Test]
    public function it_makes_form_config_instances_from_files()
    {
        $contents = "email_field: email\nlist_ids:\n  - 1";
        $item = $this->store->makeItemFromFile(Path::tidy($this->store->directory().'/test_form.yaml'), $contents);

        $this->assertInstanceOf(FormConfig::class, $item);
        $this->assertEquals('activecampaign::test_form::default', $item->id());
        $this->assertEquals('test_form', $item->handle());
        $this->assertEquals('activecampaign', $item->integration());
        $this->assertEquals('email', $item->emailField());
    }

    #[Test]
    public function it_makes_form_config_instances_from_files_when_using_multisite()
    {
        $this->setSites([
            'en' => ['url' => 'https://example.com/'],
            'nl' => ['url' => 'https://example.com/nl/'],
        ]);

        $contents = "email_field: email\nlist_ids:\n  - 1";
        $item = $this->store->makeItemFromFile(Path::tidy($this->store->directory().'/nl/test_form.yaml'), $contents);

        $this->assertInstanceOf(FormConfig::class, $item);
        $this->assertEquals('activecampaign::test_form::nl', $item->id());
        $this->assertEquals('test_form', $item->handle());
        $this->assertEquals('email', $item->emailField());
    }

    #[Test]
    public function it_migrates_legacy_singular_keys_to_plural()
    {
        $contents = "email_field: email\nlist_id: 1\ntag_id: 2";
        $item = $this->store->makeItemFromFile(Path::tidy($this->store->directory().'/test_form.yaml'), $contents);

        $this->assertEquals([1], $item->listIds());
        $this->assertEquals([2], $item->tagIds());
    }
}
