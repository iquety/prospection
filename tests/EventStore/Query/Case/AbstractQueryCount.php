<?php

declare(strict_types=1);

namespace Tests\EventStore\Query\Case;

use Iquety\Prospection\EventStore\Query;

/**
 * @method Query queryFactory
 * @method void resetDatabase
 */
trait AbstractQueryCount
{
    /** @test */
    public function countEvents(): void
    {
        $object = $this->queryFactory();
        
        $this->assertEquals(49, $object->countEvents());

        $this->resetDatabase();

        $this->assertEquals(0, $object->countEvents());
    }

    /** @test */
    public function countAggregateEvents(): void
    {
        $object = $this->queryFactory();
        
        $this->assertEquals(10, $object->countAggregateEvents('aggregate.one', '12345'));
        $this->assertEquals(10, $object->countAggregateEvents('aggregate.one', '54321+5h'));
        $this->assertEquals(10, $object->countAggregateEvents('aggregate.two', '12345'));
        $this->assertEquals(16, $object->countAggregateEvents('aggregate.thr', '67890'));

        $this->resetDatabase();

        $this->assertEquals(0, $object->countAggregateEvents('aggregate.one', '12345'));
        $this->assertEquals(0, $object->countAggregateEvents('aggregate.one', '54321+5h'));
        $this->assertEquals(0, $object->countAggregateEvents('aggregate.two', '12345'));
        $this->assertEquals(0, $object->countAggregateEvents('aggregate.thr', '67890'));
    }

    /** @test */
    public function countAggregates(): void
    {
        $object = $this->queryFactory();
        
        $this->assertEquals(5, $object->countAggregates('aggregate.one'));
        $this->assertEquals(1, $object->countAggregates('aggregate.two'));
        $this->assertEquals(1, $object->countAggregates('aggregate.thr'));

        $this->resetDatabase();

        $this->assertEquals(0, $object->countAggregates('aggregate.one'));
        $this->assertEquals(0, $object->countAggregates('aggregate.two'));
        $this->assertEquals(0, $object->countAggregates('aggregate.thr'));
    }
}
