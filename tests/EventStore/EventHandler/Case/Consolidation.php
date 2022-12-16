<?php

declare(strict_types=1);

namespace Tests\EventStore\EventHandler\Case;

use Iquety\Prospection\EventStore\Descriptor;
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
    /**
     * @test
     * @dataProvider eventStoreProvider
     */
    public function consolidationList(EventStore $object): void
    {
        $listOne = $object->listConsolidated(DummyEntityOne::class, new Interval(5));
        $this->assertCount(5, $listOne);

        $date = fn($hour) => "2022-10-10 0$hour:00:00.000000";

        $this->assertInstanceOf(Descriptor::class, $listOne[0]);
        $this->assertEquals($date(0), $listOne[0]->createdOn()->format('Y-m-d H:i:s.u'));
        $this->assertEquals($date(2), $listOne[0]->updatedOn()->format('Y-m-d H:i:s.u'));
        $this->assertEquals($date(0), $listOne[1]->createdOn()->format('Y-m-d H:i:s.u'));
        $this->assertEquals($date(2), $listOne[1]->updatedOn()->format('Y-m-d H:i:s.u'));
        $this->assertEquals($date(0), $listOne[2]->createdOn()->format('Y-m-d H:i:s.u'));
        $this->assertEquals($date(2), $listOne[2]->updatedOn()->format('Y-m-d H:i:s.u'));

        $listTwo = $object->listConsolidated(DummyEntityTwo::class, new Interval(5));
        $this->assertCount(2, $listTwo);

        $this->assertInstanceOf(Descriptor::class, $listTwo[0]);
        $this->assertEquals($date(0), $listTwo[0]->createdOn()->format('Y-m-d H:i:s.u'));
        $this->assertEquals($date(1), $listTwo[0]->updatedOn()->format('Y-m-d H:i:s.u'));
        $this->assertEquals($date(0), $listTwo[1]->createdOn()->format('Y-m-d H:i:s.u'));
        $this->assertEquals($date(1), $listTwo[1]->updatedOn()->format('Y-m-d H:i:s.u'));
    }

    /**
     * @test
     * @dataProvider eventStoreProvider
     */
    public function consolidationIntervalLimit(EventStore $object): void
    {
        $this->assertCount(0, $object->listConsolidated(DummyEntityOne::class, new Interval(0)));
        $this->assertCount(1, $object->listConsolidated(DummyEntityOne::class, new Interval(1)));
        $this->assertCount(2, $object->listConsolidated(DummyEntityOne::class, new Interval(2)));
        $this->assertCount(3, $object->listConsolidated(DummyEntityOne::class, new Interval(3)));
        $this->assertCount(4, $object->listConsolidated(DummyEntityOne::class, new Interval(4)));
        $this->assertCount(5, $object->listConsolidated(DummyEntityOne::class, new Interval(5)));
    }

    /**
     * @test
     * @dataProvider eventStoreProvider
     */
    public function consolidationIntervalOffset(EventStore $object): void
    {
        $this->resetDatabase();

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
