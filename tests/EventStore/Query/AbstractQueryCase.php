<?php

declare(strict_types=1);

namespace Tests\EventStore\Query;

use Iquety\Prospection\EventStore\Query;
use Tests\TestCase;

abstract class AbstractQueryCase extends TestCase
{
    use AbstractQueryCount;
    use AbstractQueryList;
    use AbstractQueryListDate;
    use AbstractQueryListVersion;
    use AbstractQueryListAggreg;
    use AbstractQueryListConsol;

    abstract public function queryFactory(): Query;

    /** @test */
    public function nextVersion(): void
    {
        $object = $this->queryFactory();

        $this->assertEquals(10, $object->countAggregateEvents('aggregate.one', '12345'));
        $this->assertEquals(10, $object->countAggregateEvents('aggregate.one', '54321'));
        $this->assertEquals(10, $object->countAggregateEvents('aggregate.two', '12345'));
        $this->assertEquals(16, $object->countAggregateEvents('aggregate.thr', '67890'));

        $this->assertEquals(11, $object->nextVersion('aggregate.one', '12345'));
        $this->assertEquals(11, $object->nextVersion('aggregate.one', '54321'));
        $this->assertEquals(11, $object->nextVersion('aggregate.two', '12345'));
        $this->assertEquals(17, $object->nextVersion('aggregate.thr', '67890'));

        $this->assertEquals(1, $object->nextVersion('aggregate.notexists', '12345'));
    }
}
