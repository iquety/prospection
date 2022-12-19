<?php

declare(strict_types=1);

namespace Tests\EventStore\Store;

use Iquety\Prospection\EventStore\Error;
use Iquety\Prospection\EventStore\Memory\MemoryConnection;
use Iquety\Prospection\EventStore\Memory\MemoryStore;
use Iquety\Prospection\EventStore\Store;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class MemoryStoreTest extends AbstractStoreCase
{
    public function getPersistedEvents(): array
    {
        return MemoryConnection::instance()->all();
    }

    public function resetDatabase(): void
    {
        MemoryConnection::instance()->reset();
    }

    public function storeFactory(): Store
    {
        return new MemoryStore(MemoryConnection::instance());
    }

    public function setUp(): void
    {
        $this->resetDatabase();

        $this->persistEvent('12345', 1, 1);
        $this->persistEvent('12345', 2, 0);
        $this->persistEvent('12345', 3, 0);
        $this->persistEvent('12345', 4, 0);

        $this->persistEvent('54321', 1, 1);
        $this->persistEvent('54321', 2, 0);
        $this->persistEvent('54321', 3, 0);
        $this->persistEvent('54321', 4, 0);
    }

    protected function persistEvent(string $aggregateId, int $version, int $snapshot): void
    {
        $eventData = $this->persistedEventData(
            "aggregate.one",
            "event.$version",
            $aggregateId,
            $version,
            $snapshot
        );

        MemoryConnection::instance()->add($eventData);
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
