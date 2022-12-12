<?php

declare(strict_types=1);

namespace Tests\EventStore\EventHandler;

use Iquety\Prospection\Domain\Core\IdentityObject;
use Tests\EventStore\Support\DummyEntityOne;
use Tests\EventStore\Support\DummyEntityThr;
use Tests\EventStore\Support\DummyEntityTwo;

class CountTest extends EventHandlerCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->databaseFactory();
    }

    /** @test */
    public function countAll(): void
    {
        $object = $this->eventStoreFactory();

        $this->assertEquals(5, $object->countAll());
    }

    /** @test */
    public function countAggregateEvents(): void
    {
        $object = $this->eventStoreFactory();

        $this->assertEquals(
            1,
            $object->countAggregateEvents(DummyEntityOne::class, new IdentityObject('12345'))
        );

        $this->assertEquals(
            1,
            $object->countAggregateEvents(DummyEntityOne::class, new IdentityObject('54321'))
        );
        $this->assertEquals(
            1,
            $object->countAggregateEvents(DummyEntityTwo::class, new IdentityObject('12345'))
        );
        $this->assertEquals(
            2,
            $object->countAggregateEvents(DummyEntityThr::class, new IdentityObject('67890'))
        );
    }

    /** @test */
    public function countAggregates(): void
    {
        $object = $this->eventStoreFactory();

        $this->assertEquals(2, $object->countAggregates(DummyEntityOne::class));
        $this->assertEquals(1, $object->countAggregates(DummyEntityTwo::class));
        $this->assertEquals(1, $object->countAggregates(DummyEntityThr::class));
    }
}
