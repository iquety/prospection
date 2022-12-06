<?php

declare(strict_types=1);

namespace Tests\EventStore\Query;

use Iquety\Prospection\EventStore\Memory\MemoryConnection;

trait AbstractQueryCount
{
    /** @test */
    public function countEvents(): void
    {
        $object = $this->queryFactory();
        
        $this->assertEquals(46, $object->countEvents());

        MemoryConnection::instance()->reset();

        $this->assertEquals(0, $object->countEvents());
    }

    /** @test */
    public function countAggregateEvents(): void
    {
        $object = $this->queryFactory();
        
        $this->assertEquals(10, $object->countAggregateEvents('aggregate.one', '12345'));
        $this->assertEquals(10, $object->countAggregateEvents('aggregate.one', '54321'));
        $this->assertEquals(10, $object->countAggregateEvents('aggregate.two', '12345'));
        $this->assertEquals(16, $object->countAggregateEvents('aggregate.thr', '67890'));

        MemoryConnection::instance()->reset();

        $this->assertEquals(0, $object->countAggregateEvents('aggregate.one', '12345'));
        $this->assertEquals(0, $object->countAggregateEvents('aggregate.one', '54321'));
        $this->assertEquals(0, $object->countAggregateEvents('aggregate.two', '12345'));
        $this->assertEquals(0, $object->countAggregateEvents('aggregate.thr', '67890'));
    }

    /** @test */
    public function countAggregates(): void
    {
        $object = $this->queryFactory();
        
        $this->assertEquals(2, $object->countAggregates('aggregate.one'));
        $this->assertEquals(1, $object->countAggregates('aggregate.two'));
        $this->assertEquals(1, $object->countAggregates('aggregate.thr'));

        MemoryConnection::instance()->reset();

        $this->assertEquals(0, $object->countAggregates('aggregate.one'));
        $this->assertEquals(0, $object->countAggregates('aggregate.two'));
        $this->assertEquals(0, $object->countAggregates('aggregate.thr'));
    }
}
