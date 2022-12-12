<?php

declare(strict_types=1);

namespace Tests\EventStore\Query;

use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\EventStore\Interval;
use Iquety\Prospection\EventStore\Query;

trait AbstractQueryList
{
    /** @test */
    public function snapshotListTwoEntities(): void
    {
        /** @var Query */
        $object = $this->queryFactory();
        
        $aggregateList = $object->aggregateList('aggregate.one', new Interval(999, 0));
        
        // existem duas entidades para 'aggregate.one'
        $this->assertCount(2, $aggregateList);

        // cada entidade possui 10 eventos
        // onde o evento 1 é o único snapshot
        $this->assertEquals(
            10,
            $object->countAggregateEvents('aggregate.one', '12345')
        );
        $this->assertEquals('12345', $aggregateList[0]['aggregateId']);
        // último snapshot
        $this->assertEquals('2022-10-10 01:10:10', $aggregateList[0]['occurredOn']);
        // primeiro evento
        $this->assertEquals('2022-10-10 01:10:10', $aggregateList[0]['createdOn']); 
        // último evento
        $this->assertEquals('2022-10-10 10:10:10', $aggregateList[0]['updatedOn']); 

        $this->assertEquals(
            10,
            $object->countAggregateEvents('aggregate.one', '54321')
        );
        $this->assertEquals('54321', $aggregateList[1]['aggregateId']);
        // último snapshot
        $this->assertEquals('2022-10-10 06:10:10', $aggregateList[1]['occurredOn']);
        // primeiro evento
        $this->assertEquals('2022-10-10 06:10:10', $aggregateList[1]['createdOn']);
        // último evento
        $this->assertEquals('2022-10-10 15:10:10', $aggregateList[1]['updatedOn']);
    }

    /** @test */
    public function snapshotListLimitInterval(): void
    {
        /** @var Query */
        $object = $this->queryFactory();
        
        $aggregateList = $object->aggregateList('aggregate.one', new Interval(999, 0));
        $this->assertCount(2, $aggregateList);

        $aggregateList = $object->aggregateList('aggregate.one', new Interval(2, 0));
        $this->assertCount(2, $aggregateList);

        $aggregateList = $object->aggregateList('aggregate.one', new Interval(0, 0));
        $this->assertCount(0, $aggregateList);

        $aggregateList = $object->aggregateList('aggregate.one', new Interval(2, 2));
        $this->assertCount(0, $aggregateList);
    }

    /** @test */
    public function snapshotListSameId(): void
    {
        /** @var Query */
        $object = $this->queryFactory();
        
        $aggregateOne = $object->aggregateList('aggregate.one', new Interval(1));
        
        $aggregateList = $object->aggregateList('aggregate.two', new Interval(999, 0));

        // existe uma única entidade para 'aggregate.two'
        $this->assertCount(1, $aggregateList);

        // a entidade possui 10 eventos
        // onde o evento 1 é o único snapshot
        $this->assertEquals(
            10,
            $object->countAggregateEvents('aggregate.two', '12345')
        );
        $this->assertEquals('12345', $aggregateOne[0]['aggregateId']); // aggregate.one
        $this->assertEquals('12345', $aggregateList[0]['aggregateId']); // aggregate.two
        // último snapshot
        $this->assertEquals('2022-10-10 01:10:10', $aggregateList[0]['occurredOn']);
        // primeiro evento
        $this->assertEquals('2022-10-10 01:10:10', $aggregateList[0]['createdOn']);
        // último evento
        $this->assertEquals('2022-10-10 10:10:10', $aggregateList[0]['updatedOn']);
    }

    /** @test */
    public function snapshotListTwoSnapshots(): void
    {
        /** @var Query */
        $object = $this->queryFactory();
        
        $aggregateList = $object->aggregateList('aggregate.thr', new Interval(999, 0));

        // existe uma única entidade para 'aggregate.thr'
        $this->assertCount(1, $aggregateList);

        // a entidade possui 16 eventos
        // onde os eventos 1 e 11 são snapshots
        $this->assertEquals('67890', $aggregateList[0]['aggregateId']);
        // último snapshot = evento 11
        $this->assertEquals('2022-10-10 11:10:10', $aggregateList[0]['occurredOn']);
        // primeiro evento = primeiro snapshot
        $this->assertEquals('2022-10-10 01:10:10', $aggregateList[0]['createdOn']);
        // último evento
        $this->assertEquals('2022-10-10 16:10:10', $aggregateList[0]['updatedOn']);
    }
}
