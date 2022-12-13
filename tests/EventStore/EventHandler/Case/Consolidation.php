<?php

declare(strict_types=1);

namespace Tests\EventStore\EventHandler\Case;

use Iquety\Prospection\EventStore\EventStore;
use Iquety\Prospection\EventStore\Interval;
use Tests\EventStore\Support\DummyEntityOne;
use Tests\EventStore\Support\DummyEntityTwo;
use Tests\EventStore\Support\DummyEventOne;
use Tests\EventStore\Support\DummyEventTwo;

/**
 * @method array getPersistedEvents()
 * @method EventStore eventStoreFactory()
 * @method void resetDatabase()
 */
trait Consolidation
{
    /** @test */
    public function consolidationList(): void
    {
        $object = $this->eventStoreFactory();

        $object->registerEventType(DummyEventOne::class);
        $object->registerEventType(DummyEventTwo::class);

        $this->assertCount(5, $object->listConsolidated(DummyEntityOne::class, new Interval(5)));
        $this->assertCount(2, $object->listConsolidated(DummyEntityTwo::class, new Interval(5)));
    }

    /** @test */
    public function consolidationIntervalLimit(): void
    {
        $object = $this->eventStoreFactory();

        $object->registerEventType(DummyEventOne::class);
        $object->registerEventType(DummyEventTwo::class);

        $this->assertCount(0, $object->listConsolidated(DummyEntityOne::class, new Interval(0)));
        $this->assertCount(1, $object->listConsolidated(DummyEntityOne::class, new Interval(1)));
        $this->assertCount(2, $object->listConsolidated(DummyEntityOne::class, new Interval(2)));
        $this->assertCount(3, $object->listConsolidated(DummyEntityOne::class, new Interval(3)));
        $this->assertCount(4, $object->listConsolidated(DummyEntityOne::class, new Interval(4)));
        $this->assertCount(5, $object->listConsolidated(DummyEntityOne::class, new Interval(5)));
    }

    /** @test */
    public function consolidationIntervalOffset(): void
    {
        $object = $this->eventStoreFactory();

        $object->registerEventType(DummyEventOne::class);
        $object->registerEventType(DummyEventTwo::class);

        $object->storeMultiple(DummyEntityOne::class, $this->aggregateOneListFactory('12345'));
        $object->storeMultiple(DummyEntityOne::class, $this->aggregateOneListFactory('67890'));
        $object->storeMultiple(DummyEntityOne::class, $this->aggregateOneListFactory('abcde'));
        $object->storeMultiple(DummyEntityOne::class, $this->aggregateOneListFactory('fghij'));
        $object->storeMultiple(DummyEntityOne::class, $this->aggregateOneListFactory('tuvxyz'));

        $this->assertCount(5, $object->listConsolidated(DummyEntityOne::class, new Interval(5, 0)));
        $this->assertCount(4, $object->listConsolidated(DummyEntityOne::class, new Interval(5, 1)));
        $this->assertCount(3, $object->listConsolidated(DummyEntityOne::class, new Interval(5, 2)));
        $this->assertCount(2, $object->listConsolidated(DummyEntityOne::class, new Interval(5, 3)));
        $this->assertCount(1, $object->listConsolidated(DummyEntityOne::class, new Interval(5, 4)));
        $this->assertCount(0, $object->listConsolidated(DummyEntityOne::class, new Interval(5, 5)));

        $this->assertCount(1, $object->listConsolidated(DummyEntityOne::class, new Interval(1, 0)));
        $this->assertCount(1, $object->listConsolidated(DummyEntityOne::class, new Interval(1, 1)));
        $this->assertCount(1, $object->listConsolidated(DummyEntityOne::class, new Interval(1, 2)));
        $this->assertCount(1, $object->listConsolidated(DummyEntityOne::class, new Interval(1, 3)));
        $this->assertCount(1, $object->listConsolidated(DummyEntityOne::class, new Interval(1, 4)));

        $this->assertCount(0, $object->listConsolidated(DummyEntityOne::class, new Interval(1, 5)));
    }
}
