<?php

declare(strict_types=1);

namespace Tests\EventStore\Query\Case;

use Iquety\Prospection\EventStore\Query;

/**
 * @method Query queryFactory
 * @method void resetDatabase
 */
trait AbstractQueryListVersion
{
    /** @test */
    public function eventListForVersion(): void
    {
        $object = $this->queryFactory();

        $this->assertEquals(10, $object->countAggregateEvents('aggregate.one', '12345'));

        // a partir da versão 1
        $eventList = $object->eventListForVersion('aggregate.one', '12345', 1);
        $this->assertCount(10, $eventList);
        $this->assertEquals('2022-10-10 01:10:10.000000', $eventList[0]['occurredOn']);
        $this->assertEquals('2022-10-10 10:10:10.000000', $eventList[9]['occurredOn']);

        // a partir da versão 5
        $eventList = $object->eventListForVersion('aggregate.one', '12345', 5);
        $this->assertCount(6, $eventList);
        $this->assertEquals('2022-10-10 05:10:10.000000', $eventList[0]['occurredOn']);
        $this->assertEquals('2022-10-10 10:10:10.000000', $eventList[5]['occurredOn']);
    }
}
