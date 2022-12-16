<?php

declare(strict_types=1);

namespace Tests\EventStore\Query\Case;

use Iquety\Prospection\EventStore\Interval;
use Iquety\Prospection\EventStore\Query;

/**
 * @method Query queryFactory
 * @method void resetDatabase
 */
trait AbstractQueryList
{
    /** @test */
    public function snapshotListTwoEntities(): void
    {
        $object = $this->queryFactory();

        $aggregateList = $object->aggregateList('aggregate.one', new Interval(999, 0));

        // snapshot 12345
        $this->assertSame('12345', $aggregateList[0]['aggregateId']);
        $this->assertSame(1, $aggregateList[0]['snapshot']);
        $this->assertSame(1, $aggregateList[0]['version']);
        $this->assertSame('2022-10-10 01:10:10.000000', $aggregateList[0]['occurredOn']);
        $this->assertSame('2022-10-10 01:10:10.000000', $aggregateList[0]['createdOn']);
        $this->assertSame('2022-10-10 10:10:10.000000', $aggregateList[0]['updatedOn']);

        // snapshot 54321+5h
        $this->assertEquals('54321+5h', $aggregateList[1]['aggregateId']);
        $this->assertSame(1, $aggregateList[1]['snapshot']);
        $this->assertSame(1, $aggregateList[1]['version']);
        $this->assertEquals('2022-10-10 06:10:10.000000', $aggregateList[1]['occurredOn']); // somando +5 horas
        $this->assertEquals('2022-10-10 06:10:10.000000', $aggregateList[1]['createdOn']);
        $this->assertEquals('2022-10-10 15:10:10.000000', $aggregateList[1]['updatedOn']);
    }

    /** @test */
    public function snapshotListLimitInterval(): void
    {
        $object = $this->queryFactory();

        $this->assertCount(1, $object->aggregateList('aggregate.thr', new Interval(99, 0)));

        $this->assertCount(5, $object->aggregateList('aggregate.one', new Interval(99, 0)));
        $this->assertCount(4, $object->aggregateList('aggregate.one', new Interval(99, 1)));
        $this->assertCount(3, $object->aggregateList('aggregate.one', new Interval(99, 2)));
        $this->assertCount(2, $object->aggregateList('aggregate.one', new Interval(99, 3)));
        $this->assertCount(1, $object->aggregateList('aggregate.one', new Interval(99, 4)));
        $this->assertCount(0, $object->aggregateList('aggregate.one', new Interval(99, 5)));
        $this->assertCount(0, $object->aggregateList('aggregate.one', new Interval(99, 6)));
        $this->assertCount(0, $object->aggregateList('aggregate.one', new Interval(99, 99)));

        $this->assertCount(0, $object->aggregateList('aggregate.one', new Interval(0, 0)));
        $this->assertCount(1, $object->aggregateList('aggregate.one', new Interval(1, 0)));
        $this->assertCount(2, $object->aggregateList('aggregate.one', new Interval(2, 0)));
        $this->assertCount(3, $object->aggregateList('aggregate.one', new Interval(3, 0)));
        $this->assertCount(4, $object->aggregateList('aggregate.one', new Interval(4, 0)));
        $this->assertCount(5, $object->aggregateList('aggregate.one', new Interval(5, 0)));
        $this->assertCount(5, $object->aggregateList('aggregate.one', new Interval(6, 0)));

        $this->assertCount(0, $object->aggregateList('aggregate.one', new Interval(0, 0)));
        $this->assertCount(1, $object->aggregateList('aggregate.one', new Interval(1, 1)));
        $this->assertCount(2, $object->aggregateList('aggregate.one', new Interval(2, 2)));
        $this->assertCount(2, $object->aggregateList('aggregate.one', new Interval(3, 3)));
        $this->assertCount(1, $object->aggregateList('aggregate.one', new Interval(4, 4)));
        $this->assertCount(0, $object->aggregateList('aggregate.one', new Interval(5, 5)));
    }

    /** @test */
    public function snapshotListSameId(): void
    {
        $object = $this->queryFactory();

        $aggregateOne = $object->aggregateList('aggregate.one', new Interval(999));
        $aggregateList = $object->aggregateList('aggregate.two', new Interval(999, 0));

        $this->assertCount(5, $aggregateOne);
        $this->assertCount(1, $aggregateList);

        $this->assertSame(10, $object->countAggregateEvents('aggregate.two', '12345'));
        $this->assertSame('12345', $aggregateOne[0]['aggregateId']); // aggregate.one
        $this->assertSame('12345', $aggregateList[0]['aggregateId']); // aggregate.two
    }

    /** @test */
    public function snapshotListTwoSnapshots(): void
    {
        $object = $this->queryFactory();

        $aggregateList = $object->aggregateList('aggregate.thr', new Interval(999, 0));

        // existe uma única entidade para 'aggregate.thr'
        $this->assertCount(1, $aggregateList);

        // snapshot = evento 11
        $this->assertSame('67890', $aggregateList[0]['aggregateId']);
        $this->assertSame(1, $aggregateList[0]['snapshot']);
        $this->assertSame(11, $aggregateList[0]['version']);
        $this->assertSame('2022-10-10 11:10:10.000000', $aggregateList[0]['occurredOn']);
        $this->assertSame('2022-10-10 01:10:10.000000', $aggregateList[0]['createdOn']);
        $this->assertSame('2022-10-10 16:10:10.000000', $aggregateList[0]['updatedOn']);
    }
}
