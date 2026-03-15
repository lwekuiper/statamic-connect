<?php

namespace Lwekuiper\StatamicConnect\Tests\Unit;

use Lwekuiper\StatamicConnect\IntegrationManager;
use Lwekuiper\StatamicConnect\Tests\TestCase;

class IntegrationManagerTest extends TestCase
{
    protected IntegrationManager $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = new IntegrationManager;
    }

    public function test_can_register_and_retrieve_integration(): void
    {
        $this->manager->register(StubIntegration::class);

        $this->assertCount(1, $this->manager->all());
        $this->assertEquals('stub', $this->manager->all()->first()->name());
    }

    public function test_find_returns_integration_by_name(): void
    {
        $this->manager->register(StubIntegration::class);

        $integration = $this->manager->find('stub');

        $this->assertNotNull($integration);
        $this->assertEquals('stub', $integration->name());
    }

    public function test_find_returns_null_for_unknown_integration(): void
    {
        $this->assertNull($this->manager->find('nonexistent'));
    }

    public function test_all_returns_collection(): void
    {
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $this->manager->all());
    }
}
