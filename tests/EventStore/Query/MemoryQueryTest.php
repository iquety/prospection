<?php

declare(strict_types=1);

namespace Tests\EventStore\Query;

use DateTimeImmutable;
use Iquety\Prospection\EventStore\Error;
use Iquety\Prospection\EventStore\EventSnapshot;
use Iquety\Prospection\EventStore\Memory\MemoryConnection;
use Iquety\Prospection\EventStore\Memory\MemoryQuery;
use Iquety\Prospection\EventStore\Query;

class MemoryQueryTest extends AbstractQueryCase
{
    public function queryFactory(): Query
    {
        return new MemoryQuery(MemoryConnection::instance());
    }
    
    public function setUp(): void
    {
        MemoryConnection::instance()->reset();

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
        // + 1 snapshot para cada agregado
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - 

        $version = 1;

        MemoryConnection::instance()->add(
            $this->snapshotDataFactory('aggregate.one', '12345', $version)
        );

        MemoryConnection::instance()->add( // mesmo agregado, id diferente
            $this->snapshotDataFactory('aggregate.one', '54321', $version)
        );

        MemoryConnection::instance()->add( // agregado diferente, mesmo id
            $this->snapshotDataFactory('aggregate.two', '12345', $version)
        );

        MemoryConnection::instance()->add( // tudo diferente
            $this->snapshotDataFactory('aggregate.thr', '67890', $version)
        );

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
        // + 9 eventos para cada agregado
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - 

        array_map(function() use (&$version) {
            $version++;

            MemoryConnection::instance()->add($this->eventDataFactory('aggregate.one', '12345', $version));
            MemoryConnection::instance()->add($this->eventDataFactory('aggregate.one', '54321', $version));
            MemoryConnection::instance()->add($this->eventDataFactory('aggregate.two', '12345', $version));
            MemoryConnection::instance()->add($this->eventDataFactory('aggregate.thr', '67890', $version));
        }, range(1,9));
        

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
        // + 1 snapshot para o terceiro agregado
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - 

        $version++;

        MemoryConnection::instance()->add(
            $this->snapshotDataFactory('aggregate.thr', '67890', $version)
        );

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - 
        // + 5 eventos para o terceiro agregado
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - - 

        array_map(function() use (&$version) {
            $version++;
            MemoryConnection::instance()->add($this->eventDataFactory('aggregate.thr', '67890', $version));
            
        }, range(1, 5));
    }

    private function eventDataFactory(string $aggregateLabel, string $id, int $version): array
    {
        $now = new DateTimeImmutable("2022-10-10 00:10:10");
        $now = $now->modify("+$version hours");

        if ($id === '54321') {
            $now = $now->modify("+5 hours");
        }

        return [
            'aggregateId'    => $id,
            'aggregateLabel' => $aggregateLabel,
            'eventLabel'     => md5($now->format('Y-m-d H:i:s')),
            'version'        => $version,
            'snapshot'       => 0,
            'eventData'      => json_encode([]),
            'occurredOn'     => $now->format('Y-m-d H:i:s')
        ];
    }

    private function snapshotDataFactory(string $aggregateLabel, string $id, int $version): array
    {
        $now = new DateTimeImmutable("2022-10-10 00:10:10");
        $now = $now->modify("+$version hours");

        if ($id === '54321') {
            $now = $now->modify("+5 hours");
        }

        return [
            'aggregateId'    => $id,
            'aggregateLabel' => $aggregateLabel,
            'eventLabel'     => EventSnapshot::label(),
            'version'        => $version,
            'snapshot'       => 1,
            'eventData'      => json_encode([]),
            'occurredOn'     => $now->format('Y-m-d H:i:s')
        ];
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
