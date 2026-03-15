<?php

namespace Lwekuiper\StatamicConnect\Tests\Feature;

use Lwekuiper\StatamicConnect\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Addon;

class EditionRestrictionTest extends TestCase
{
    #[Test]
    public function it_defaults_to_lite_edition()
    {
        $addon = Addon::get('lwekuiper/statamic-connect');

        $this->assertNotNull($addon);
    }
}
