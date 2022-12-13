<?php

declare(strict_types=1);

namespace Tests\EventStore\Query\Case;

use Iquety\Prospection\EventStore\Interval;
use Iquety\Prospection\EventStore\Query;

/**
 * @method Query queryFactory
 * @method void resetDatabase
 */
trait AbstractQueryListConsol
{
    /** @test */
    public function emptyEventListForConsolidation(): void
    {
        $object = $this->queryFactory();
        
        $eventsAfterSnapshot = $object->eventListForConsolidation([]);

        // total de eventos do agregado é 0
        $this->assertCount(0, $eventsAfterSnapshot);
    }

    /** @test */
    public function eventListForThrConsolidation(): void
    {
        /** @var Query */
        $object = $this->queryFactory();
        
        $eventsAfterSnapshot = $object->eventListForConsolidation(
            $object->aggregateList('aggregate.thr', new Interval(999)),
        );

        // total de eventos do agregado é 16
        $this->assertCount(6, $eventsAfterSnapshot);
        
        $this->assertEquals(11, $eventsAfterSnapshot[0]['version']);
        $this->assertEquals('2022-10-10 11:10:10.000000', $eventsAfterSnapshot[0]['occurredOn']);

        $this->assertEquals(16, $eventsAfterSnapshot[5]['version']);
        $this->assertEquals('2022-10-10 16:10:10.000000', $eventsAfterSnapshot[5]['occurredOn']);

        // o último snapshot está na versão 11
        $aggregateEvents = $object->eventListForAggregate('aggregate.thr', '67890');
        $this->assertCount(6, $aggregateEvents);

        $this->assertEquals($aggregateEvents[0]['version'], $eventsAfterSnapshot[0]['version']);
        $this->assertEquals(
            $aggregateEvents[0]['occurredOn'],
            $eventsAfterSnapshot[0]['occurredOn']
        );
        
        $this->assertEquals($aggregateEvents[5]['version'], $eventsAfterSnapshot[5]['version']);
        $this->assertEquals(
            $aggregateEvents[5]['occurredOn'],
            $eventsAfterSnapshot[5]['occurredOn']
        );
    }

    /** @test */
    public function eventListForManyConsolidation(): void
    {
        $object = $this->queryFactory();

        // existem duas entidades para aggregate.one
        $aggregatesList = $object->aggregateList('aggregate.one', new Interval(999));
        $this->assertCount(5, $aggregatesList);

        // todos os eventos para as duas entidades
        $eventsAfterSnapshot = $object->eventListForConsolidation($aggregatesList);

        // ambas possuem 10 eventos desde o último instantâneo
        $this->assertCount(23, $eventsAfterSnapshot);
        
        // o último snapshot está na versão 1
        $aggregateEvents = $object->eventListForAggregate('aggregate.one', '12345');
        $this->assertCount(10, $aggregateEvents);

        // aggregate.one 12345
        $this->assertEquals(1, $eventsAfterSnapshot[0]['version']);
        $this->assertEquals('2022-10-10 01:10:10.000000', $eventsAfterSnapshot[0]['occurredOn']);

        $this->assertEquals(10, $eventsAfterSnapshot[9]['version']);
        $this->assertEquals('2022-10-10 10:10:10.000000', $eventsAfterSnapshot[9]['occurredOn']);

        // aggregate.one 54321
        $this->assertEquals(1, $eventsAfterSnapshot[10]['version']);
        $this->assertEquals('2022-10-10 06:10:10.000000', $eventsAfterSnapshot[10]['occurredOn']);

        $this->assertEquals(10, $eventsAfterSnapshot[19]['version']);
        $this->assertEquals('2022-10-10 15:10:10.000000', $eventsAfterSnapshot[19]['occurredOn']);
    }
}
