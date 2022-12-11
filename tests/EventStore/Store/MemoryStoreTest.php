<?php

declare(strict_types=1);

namespace Tests\EventStore\Store;

use DateTimeImmutable;
use Iquety\Prospection\EventStore\Error;
use Iquety\Prospection\EventStore\Memory\MemoryConnection;
use Iquety\Prospection\EventStore\Memory\MemoryStore;
use Iquety\Prospection\EventStore\Store;

class MemoryStoreTest extends AbstractStoreCase
{
    public function setUp(): void
    {
        MemoryConnection::instance()->reset();

        MemoryConnection::instance()->add($this->eventDataFactory('aggregate.one', '12345', 1, 1));
        MemoryConnection::instance()->add($this->eventDataFactory('aggregate.one', '12345', 2));
        MemoryConnection::instance()->add($this->eventDataFactory('aggregate.one', '12345', 3));
        MemoryConnection::instance()->add($this->eventDataFactory('aggregate.one', '12345', 4));

        MemoryConnection::instance()->add($this->eventDataFactory('aggregate.one', '54321', 1, 1));
        MemoryConnection::instance()->add($this->eventDataFactory('aggregate.one', '54321', 2));
        MemoryConnection::instance()->add($this->eventDataFactory('aggregate.one', '54321', 3));
        MemoryConnection::instance()->add($this->eventDataFactory('aggregate.one', '54321', 4));
    }

    public function storeFactory(): Store
    {
        return new MemoryStore(MemoryConnection::instance());
    }

    private function eventDataFactory(
        string $aggregateLabel,
        string $id,
        int $version,
        int $snapshot = 0
    ): array {
        $now = new DateTimeImmutable("2022-10-10 00:10:10");
        $now = $now->modify("+$version hours");

        return [
            'aggregateId'    => $id,
            'aggregateLabel' => $aggregateLabel,
            'eventLabel'     => md5($now->format('Y-m-d H:i:s')),
            'version'        => $version,
            'snapshot'       => $snapshot,
            'eventData'      => json_encode([]),
            'occurredOn'     => $now
        ];
    }

    /** @test */
    public function errors(): void
    {
        $object = $this->storeFactory();

        // MemoryStore nunca possui erros
        $this->assertFalse($object->hasError());
        $this->assertInstanceOf(Error::class, $object->lastError());
        $this->assertEquals('', $object->lastError()->code());
        $this->assertEquals('', $object->lastError()->message());
    }
}