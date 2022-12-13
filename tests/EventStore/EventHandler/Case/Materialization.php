<?php

declare(strict_types=1);

namespace Tests\EventStore\EventHandler\Case;

use DateTimeImmutable;
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
trait Materialization
{
    /** @test */
    public function materialization(): void
    {
        $object = $this->eventStoreFactory();

        $object->registerEventType(DummyEventOne::class);
        $object->registerEventType(DummyEventTwo::class);

        $this->assertCount(5, $object->listMaterialization(
                DummyEntityOne::class,
                new DateTimeImmutable('2022-10-10 00:10:10.777777'),
                new Interval(5)
        ));
        $this->assertCount(2, $object->listMaterialization(
            DummyEntityTwo::class,
            new DateTimeImmutable('2022-10-10 00:10:10.777777'),
            new Interval(5)
        ));
    }

    /** @test */
    public function materializationIntervalLimit(): void
    {
        $object = $this->eventStoreFactory();

        $object->registerEventType(DummyEventOne::class);
        $object->registerEventType(DummyEventTwo::class);

        // agregado 1
        $object->storeMultiple(DummyEntityOne::class, $this->aggregateOneListFactory('12345'));
        $object->storeMultiple(DummyEntityOne::class, $this->aggregateOneListFactory('67890'));
        $object->storeMultiple(DummyEntityOne::class, $this->aggregateOneListFactory('abcde'));
        $object->storeMultiple(DummyEntityOne::class, $this->aggregateOneListFactory('fghij'));
        $object->storeMultiple(DummyEntityOne::class, $this->aggregateOneListFactory('tuvxyz'));

        foreach (range(0, 5) as $limit) {
            $this->assertCount(
                $limit,
                $object->list(DummyEntityOne::class, new Interval($limit))
            );
        }
    }

    /** @test */
    public function materializationIntervalOffset(): void
    {
        $object = $this->eventStoreFactory();

        $object->registerEventType(DummyEventOne::class);
        $object->registerEventType(DummyEventTwo::class);

        // agregado 1
        $object->storeMultiple(DummyEntityOne::class, $this->aggregateOneListFactory('12345'));
        $object->storeMultiple(DummyEntityOne::class, $this->aggregateOneListFactory('67890'));
        $object->storeMultiple(DummyEntityOne::class, $this->aggregateOneListFactory('abcde'));
        $object->storeMultiple(DummyEntityOne::class, $this->aggregateOneListFactory('fghij'));
        $object->storeMultiple(DummyEntityOne::class, $this->aggregateOneListFactory('tuvxyz'));

        foreach (range(0, 5) as $offset) {
            $this->assertCount(
                5 - $offset,
                $object->list(DummyEntityOne::class, new Interval(5, $offset))
            );
        }

        foreach (range(0, 4) as $offset) {
            $this->assertCount(
                1,
                $object->list(DummyEntityOne::class, new Interval(1, $offset))
            );
        }

        $this->assertCount(
            0,
            $object->list(DummyEntityOne::class, new Interval(1, 5))
        );
    }
}
