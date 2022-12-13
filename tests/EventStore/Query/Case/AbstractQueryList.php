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
        
        // existem duas entidades para 'aggregate.one'
        $this->assertCount(5, $aggregateList);

        // cada entidade possui 10 eventos
        // onde o evento 1 é o único snapshot
        $this->assertEquals(10, $object->countAggregateEvents('aggregate.one', '12345'));
        $this->assertEquals('12345', $aggregateList[0]['aggregateId']);
        // último snapshot
        $this->assertEquals('2022-10-10 01:10:10.000000', $aggregateList[0]['occurredOn']);
        // primeiro evento
        $this->assertEquals('2022-10-10 01:10:10.000000', $aggregateList[0]['createdOn']); 
        // último evento
        $this->assertEquals('2022-10-10 10:10:10.000000', $aggregateList[0]['updatedOn']); 

        $this->assertEquals(10, $object->countAggregateEvents('aggregate.one', '54321+5h'));
        $this->assertEquals('54321+5h', $aggregateList[1]['aggregateId']);
        // último snapshot
        $this->assertEquals('2022-10-10 06:10:10.000000', $aggregateList[1]['occurredOn']);
        // primeiro evento
        $this->assertEquals('2022-10-10 06:10:10.000000', $aggregateList[1]['createdOn']);
        // último evento
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
        
        $aggregateOne = $object->aggregateList('aggregate.one', new Interval(1));
        
        $aggregateList = $object->aggregateList('aggregate.two', new Interval(999, 0));

        // existe uma única entidade para 'aggregate.two'
        $this->assertCount(1, $aggregateList);

        // a entidade possui 10 eventos
        // onde o evento 1 é o único snapshot
        $this->assertEquals(10, $object->countAggregateEvents('aggregate.two', '12345'));
        $this->assertEquals('12345', $aggregateOne[0]['aggregateId']); // aggregate.one
        $this->assertEquals('12345', $aggregateList[0]['aggregateId']); // aggregate.two
        // último snapshot
        $this->assertEquals('2022-10-10 01:10:10.000000', $aggregateList[0]['occurredOn']);
        // primeiro evento
        $this->assertEquals('2022-10-10 01:10:10.000000', $aggregateList[0]['createdOn']);
        // último evento
        $this->assertEquals('2022-10-10 10:10:10.000000', $aggregateList[0]['updatedOn']);
    }

    /** @test */
    public function snapshotListTwoSnapshots(): void
    {
        $object = $this->queryFactory();
        
        $aggregateList = $object->aggregateList('aggregate.thr', new Interval(999, 0));

        // existe uma única entidade para 'aggregate.thr'
        $this->assertCount(1, $aggregateList);

        // a entidade possui 16 eventos
        // onde os eventos 1 e 11 são snapshots
        $this->assertEquals('67890', $aggregateList[0]['aggregateId']);
        // último snapshot = evento 11
        $this->assertEquals('2022-10-10 11:10:10.000000', $aggregateList[0]['occurredOn']);
        // primeiro evento = primeiro snapshot
        $this->assertEquals('2022-10-10 01:10:10.000000', $aggregateList[0]['createdOn']);
        // último evento
        $this->assertEquals('2022-10-10 16:10:10.000000', $aggregateList[0]['updatedOn']);
    }
}
