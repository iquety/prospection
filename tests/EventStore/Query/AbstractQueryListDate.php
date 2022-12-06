<?php

declare(strict_types=1);

namespace Tests\EventStore\Query;

use DateTimeImmutable;
use Iquety\Prospection\EventStore\Interval;

trait AbstractQueryListDate
{
    /** @test */
    public function snapshotDateTwoEntities(): void
    {
        $object = $this->queryFactory();
        
        $aggregateList = $object->aggregateListByDate(
            'aggregate.one',
            new DateTimeImmutable("2022-10-10 00:10:10"),
            new Interval(999, 0)
        );
        
        // existem duas entidades para 'aggregate.one'
        $this->assertCount(2, $aggregateList);

        // cada entidade possui 10 eventos
        // onde o evento 1 é o único snapshot
        $this->assertEquals(10, $object->countAggregateEvents('aggregate.one', '12345'));
        $this->assertEquals('12345', $aggregateList[0]['aggregateId']);
        // último snapshot
        $this->assertEquals('2022-10-10 01:10:10', $aggregateList[0]['occurredOn']);
        // primeiro evento
        $this->assertEquals('2022-10-10 01:10:10', $aggregateList[0]['createdOn']); 
        // último evento
        $this->assertEquals('2022-10-10 10:10:10', $aggregateList[0]['updatedOn']); 

        $this->assertEquals(10, $object->countAggregateEvents('aggregate.one', '54321'));
        $this->assertEquals('54321', $aggregateList[1]['aggregateId']);
        // último snapshot
        $this->assertEquals('2022-10-10 06:10:10', $aggregateList[1]['occurredOn']);
        // primeiro evento
        $this->assertEquals('2022-10-10 06:10:10', $aggregateList[1]['createdOn']);
        // último evento
        $this->assertEquals('2022-10-10 15:10:10', $aggregateList[1]['updatedOn']);
    }

    /** @test */
    public function snapshotDateLimitByDate(): void
    {
        $object = $this->queryFactory();
        
        $aggregateList = $object->aggregateListByDate(
            'aggregate.one',
            new DateTimeImmutable("2022-10-10 05:10:10"),
            new Interval(999, 0)
        );
        
        // existe uma entidade após 05:10:10 para 'aggregate.one'
        $this->assertCount(1, $aggregateList);

        // a entidade possui 10 eventos
        // onde o evento 1 é o único snapshot
        $this->assertEquals(10, $object->countAggregateEvents('aggregate.one', '54321'));
        $this->assertEquals('54321', $aggregateList[0]['aggregateId']);
        // último snapshot
        $this->assertEquals('2022-10-10 06:10:10', $aggregateList[0]['occurredOn']);
        // primeiro evento
        $this->assertEquals('2022-10-10 06:10:10', $aggregateList[0]['createdOn']);
        // último evento
        $this->assertEquals('2022-10-10 15:10:10', $aggregateList[0]['updatedOn']);
    }

    /** @test */
    public function snapshotDateLimitInterval(): void
    {
        $object = $this->queryFactory();
        
        $aggregateList = $object->aggregateListByDate(
            'aggregate.one',
            new DateTimeImmutable("2022-10-10 05:10:10"),
            new Interval(1, 0)
        );
        $this->assertCount(1, $aggregateList);

        $aggregateList = $object->aggregateListByDate(
            'aggregate.one',
            new DateTimeImmutable("2022-10-10 05:10:10"),
            new Interval(0, 0)
        );
        $this->assertCount(0, $aggregateList);

        $aggregateList = $object->aggregateListByDate(
            'aggregate.one',
            new DateTimeImmutable("2022-10-10 05:10:10"),
            new Interval(1, 1)
        );
        $this->assertCount(0, $aggregateList);
    }
}
