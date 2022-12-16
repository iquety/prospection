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

        $this->assertCount(0, $object->eventListForConsolidation([]));
    }

    /** @test */
    public function eventListForThrConsolidation(): void
    {
        $object = $this->queryFactory();

        $eventsAfterSnapshot = $object->eventListForConsolidation(
            $object->aggregateList('aggregate.thr', new Interval(999)),
        );

        // total de eventos do agregado é 16
        $this->assertCount(6, $eventsAfterSnapshot);

        $this->assertSame(11, $eventsAfterSnapshot[0]['version']);
        $this->assertSame('2022-10-10 11:10:10.000000', $eventsAfterSnapshot[0]['occurredOn']);

        $this->assertSame(16, $eventsAfterSnapshot[5]['version']);
        $this->assertSame('2022-10-10 16:10:10.000000', $eventsAfterSnapshot[5]['occurredOn']);

        // o último snapshot está na versão 11
        $aggregateEvents = $object->eventListForAggregate('aggregate.thr', '67890');
        $this->assertCount(6, $aggregateEvents);

        $this->assertSame($aggregateEvents[0]['version'], $eventsAfterSnapshot[0]['version']);
        $this->assertSame(
            $aggregateEvents[0]['occurredOn'],
            $eventsAfterSnapshot[0]['occurredOn']
        );

        $this->assertSame($aggregateEvents[5]['version'], $eventsAfterSnapshot[5]['version']);
        $this->assertSame(
            $aggregateEvents[5]['occurredOn'],
            $eventsAfterSnapshot[5]['occurredOn']
        );
    }

    /** @test */
    public function eventListForManyConsolidation(): void
    {
        $object = $this->queryFactory();

        // aggregate.one = 5 identidades
        $aggregatesList = $object->aggregateList('aggregate.one', new Interval(999));
        $this->assertCount(5, $aggregatesList);

        $eventsAfterSnapshot = $object->eventListForConsolidation($aggregatesList);

        // ambas possuem 10 eventos desde o último instantâneo
        $this->assertCount(23, $eventsAfterSnapshot);

        $onlyId = fn($id, $list) => array_filter($list, fn($item) => $item['aggregateId'] === $id);

        $this->assertCount(10, $onlyId('12345', $eventsAfterSnapshot));
        $this->assertCount(10, $onlyId('54321+5h', $eventsAfterSnapshot));
        $this->assertCount(1, $onlyId('abcde', $eventsAfterSnapshot));
        $this->assertCount(1, $onlyId('fghij', $eventsAfterSnapshot));
        $this->assertCount(1, $onlyId('klmno', $eventsAfterSnapshot));

        // aggregate.one 12345 snapshot
        $this->assertSame('12345', $eventsAfterSnapshot[0]['aggregateId']);
        $this->assertSame(1, $eventsAfterSnapshot[0]['version']);
        $this->assertSame(1, $eventsAfterSnapshot[0]['snapshot']);
        $this->assertSame('2022-10-10 01:10:10.000000', $eventsAfterSnapshot[0]['occurredOn']);
        $this->assertSame('2022-10-10 01:10:10.000000', $eventsAfterSnapshot[0]['createdOn']);
        $this->assertSame('2022-10-10 10:10:10.000000', $eventsAfterSnapshot[0]['updatedOn']);
        // último evento
        $this->assertSame('12345', $eventsAfterSnapshot[9]['aggregateId']);
        $this->assertSame(10, $eventsAfterSnapshot[9]['version']);
        $this->assertSame(0, $eventsAfterSnapshot[9]['snapshot']);
        $this->assertSame('2022-10-10 10:10:10.000000', $eventsAfterSnapshot[9]['occurredOn']);
        $this->assertSame('2022-10-10 01:10:10.000000', $eventsAfterSnapshot[9]['createdOn']);
        $this->assertSame('2022-10-10 10:10:10.000000', $eventsAfterSnapshot[9]['updatedOn']);

        // aggregate.one 54321+5h snapshot
        $this->assertSame('54321+5h', $eventsAfterSnapshot[10]['aggregateId']);
        $this->assertSame(1, $eventsAfterSnapshot[10]['version']);
        $this->assertSame(1, $eventsAfterSnapshot[10]['snapshot']);
        $this->assertSame('2022-10-10 06:10:10.000000', $eventsAfterSnapshot[10]['occurredOn']);
        $this->assertSame('2022-10-10 06:10:10.000000', $eventsAfterSnapshot[10]['createdOn']);
        $this->assertSame('2022-10-10 15:10:10.000000', $eventsAfterSnapshot[10]['updatedOn']);
        // último evento
        $this->assertSame('54321+5h', $eventsAfterSnapshot[19]['aggregateId']);
        $this->assertSame(10, $eventsAfterSnapshot[19]['version']);
        $this->assertSame(0, $eventsAfterSnapshot[19]['snapshot']);
        $this->assertSame('2022-10-10 15:10:10.000000', $eventsAfterSnapshot[19]['occurredOn']);
        $this->assertSame('2022-10-10 06:10:10.000000', $eventsAfterSnapshot[19]['createdOn']);
        $this->assertSame('2022-10-10 15:10:10.000000', $eventsAfterSnapshot[19]['updatedOn']);

        // aggregate.one abcde snapshot
        $this->assertSame('abcde', $eventsAfterSnapshot[20]['aggregateId']);
        $this->assertSame(1, $eventsAfterSnapshot[20]['version']);
        $this->assertSame(1, $eventsAfterSnapshot[20]['snapshot']);

        // aggregate.one fghij snapshot
        $this->assertSame('fghij', $eventsAfterSnapshot[21]['aggregateId']);
        $this->assertSame(1, $eventsAfterSnapshot[21]['version']);
        $this->assertSame(1, $eventsAfterSnapshot[21]['snapshot']);

        // aggregate.one klmno snapshot
        $this->assertSame('klmno', $eventsAfterSnapshot[22]['aggregateId']);
        $this->assertSame(1, $eventsAfterSnapshot[22]['version']);
        $this->assertSame(1, $eventsAfterSnapshot[22]['snapshot']);
    }
}
