<?php

declare(strict_types=1);

namespace Tests\EventStore\Query;

use Iquety\Prospection\EventStore\Error;
use Iquety\Prospection\EventStore\Memory\MemoryConnection;
use Iquety\Prospection\EventStore\Memory\MemoryQuery;
use Iquety\Prospection\EventStore\Query;
use Tests\EventStore\Query\Case\AbstractCase;

class MemoryQueryTest extends AbstractCase
{
    public function queryFactory(): Query
    {
        return new MemoryQuery(MemoryConnection::instance());
    }

    public function resetDatabase(): void
    {
        MemoryConnection::instance()->reset();
    }

    public function setUp(): void
    {
        $this->resetDatabase();

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // + 1 snapshot para cada agregado
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $version = 1;

        $this->persistEvent('aggregate.one', '12345', $version, 1);
        $this->persistEvent('aggregate.one', '54321+5h', $version, 1); // id diferente
        $this->persistEvent('aggregate.one', 'abcde', $version, 1);
        $this->persistEvent('aggregate.one', 'fghij', $version, 1);
        $this->persistEvent('aggregate.one', 'klmno', $version, 1);
        $this->persistEvent('aggregate.two', '12345', $version, 1); // aggregado diferente
        $this->persistEvent('aggregate.thr', '67890', $version, 1); // tudo diferente

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // + 9 eventos para cada agregado
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        array_map(function () use (&$version) {
            $version++;

            $this->persistEvent('aggregate.one', '12345', $version, 0);
            $this->persistEvent('aggregate.one', '54321+5h', $version, 0);
            $this->persistEvent('aggregate.two', '12345', $version, 0);
            $this->persistEvent('aggregate.thr', '67890', $version, 0);
        }, range(1, 9));

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // + 1 snapshot para o terceiro agregado
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $version++;

        $this->persistEvent('aggregate.thr', '67890', $version, 1);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // + 5 eventos para o terceiro agregado
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        array_map(function () use (&$version) {
            $version++;

            $this->persistEvent('aggregate.thr', '67890', $version, 0);
        }, range(1, 5));
    }

    protected function persistEvent(
        string $aggregateLabel,
        string $id,
        int $version,
        int $snapshot
    ): void {
        $eventData = $this->persistedEventData(
            $aggregateLabel,
            "event.$version",
            $id,
            $version,
            $snapshot
        );

        MemoryConnection::instance()->add($eventData);
    }


    /** @test */
    public function errors(): void
    {
        $object = $this->queryFactory();

        // MemoryQuery nunca possui erros
        $this->assertFalse($object->hasError());
        $this->assertInstanceOf(Error::class, $object->lastError());
        $this->assertEquals('', $object->lastError()->code());
        $this->assertEquals('', $object->lastError()->message());
    }
}
