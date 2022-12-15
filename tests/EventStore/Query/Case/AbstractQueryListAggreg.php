<?php

declare(strict_types=1);

namespace Tests\EventStore\Query\Case;

use Iquety\Prospection\EventStore\Query;

/**
 * @method Query queryFactory
 * @method void resetDatabase
 */
trait AbstractQueryListAggreg
{
    /** @test */
    public function eventListForAggregate(): void
    {
        $object = $this->queryFactory();

        // aggregate.thr = 16 eventos
        $this->assertCount(16, $object->eventListForVersion('aggregate.thr', '67890', 1));
        
        // aggregate.thr = 6 eventos desde o último snapshot
        $aggregateEvents = $object->eventListForAggregate('aggregate.thr', '67890');
        $this->assertCount(6, $aggregateEvents);

        // primeiro evento: snapshot
        $this->assertSame('67890', $aggregateEvents[0]['aggregateId']);
        $this->assertSame(1, $aggregateEvents[0]['snapshot']);
        $this->assertSame(11, $aggregateEvents[0]['version']);
        $this->assertSame('2022-10-10 11:10:10.000000', $aggregateEvents[0]['occurredOn']);
        $this->assertSame('2022-10-10 01:10:10.000000', $aggregateEvents[0]['createdOn']);
        $this->assertSame('2022-10-10 16:10:10.000000', $aggregateEvents[0]['updatedOn']);

        // último evento
        $this->assertSame('67890', $aggregateEvents[5]['aggregateId']);
        $this->assertSame(0, $aggregateEvents[5]['snapshot']);
        $this->assertSame(16, $aggregateEvents[5]['version']);
        $this->assertSame('2022-10-10 16:10:10.000000', $aggregateEvents[5]['occurredOn']);
        $this->assertSame('2022-10-10 01:10:10.000000', $aggregateEvents[0]['createdOn']);
        $this->assertSame('2022-10-10 16:10:10.000000', $aggregateEvents[0]['updatedOn']);
    }
}
