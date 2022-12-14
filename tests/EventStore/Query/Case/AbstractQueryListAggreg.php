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
        
        $allEvents = $object->eventListForVersion('aggregate.thr', '67890', 1);

        // total de eventos do agregado é 16
        $this->assertCount(16, $allEvents);
        
        // o último snapshot está na versão 11
        $aggregateEvents = $object->eventListForAggregate('aggregate.thr', '67890');

        $this->assertCount(6, $aggregateEvents);

        $this->assertEquals(11, $aggregateEvents[0]['version']);
        $this->assertEquals('2022-10-10 11:10:10.000000', $aggregateEvents[0]['occurredOn']);
        $this->assertEquals('2022-10-10 11:10:10.000000', $aggregateEvents[0]['createdOn']);
        $this->assertEquals('2022-10-10 16:10:10.000000', $aggregateEvents[0]['updatedOn']);

        $this->assertEquals(16, $aggregateEvents[5]['version']);
        $this->assertEquals('2022-10-10 16:10:10.000000', $aggregateEvents[5]['occurredOn']);
        $this->assertEquals('2022-10-10 11:10:10.000000', $aggregateEvents[0]['createdOn']);
        $this->assertEquals('2022-10-10 16:10:10.000000', $aggregateEvents[0]['updatedOn']);
    }
}