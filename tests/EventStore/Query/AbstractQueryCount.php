<?php

declare(strict_types=1);

namespace Tests\EventStore\Query;

use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\EventStore\Memory\MemoryConnection;
use Iquety\Prospection\EventStore\Query;

trait AbstractQueryCount
{
    /** @test */
    public function countEvents(): void
    {
        /** @var Query */
        $object = $this->queryFactory();
        
        $this->assertEquals(46, $object->countEvents());

        MemoryConnection::instance()->reset();

        $this->assertEquals(0, $object->countEvents());
    }

    /** @test */
    public function countAggregateEvents(): void
    {
        /** @var Query */
        $object = $this->queryFactory();
        
        $this->assertEquals(
            10,
            $object->countAggregateEvents('aggregate.one', new IdentityObject('12345'))
        );
        $this->assertEquals(
            10,
            $object->countAggregateEvents('aggregate.one', new IdentityObject('54321'))
        );
        $this->assertEquals(
            10,
            $object->countAggregateEvents('aggregate.two', new IdentityObject('12345'))
        );
        $this->assertEquals(
            16,
            $object->countAggregateEvents('aggregate.thr', new IdentityObject('67890'))
        );

        MemoryConnection::instance()->reset();

        $this->assertEquals(
            0,
            $object->countAggregateEvents('aggregate.one', new IdentityObject('12345'))
        );
        $this->assertEquals(
            0,
            $object->countAggregateEvents('aggregate.one', new IdentityObject('54321'))
        );
        $this->assertEquals(
            0,
            $object->countAggregateEvents('aggregate.two', new IdentityObject('12345'))
        );
        $this->assertEquals(
            0,
            $object->countAggregateEvents('aggregate.thr', new IdentityObject('67890'))
        );
    }

    /** @test */
    public function countAggregates(): void
    {
        /** @var Query */
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
