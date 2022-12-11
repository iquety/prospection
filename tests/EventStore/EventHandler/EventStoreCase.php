<?php

declare(strict_types=1);

namespace Tests\EventStore\EventHandler;

use Iquety\Prospection\EventStore\EventStore;
use Iquety\Prospection\EventStore\Memory\MemoryConnection;
use Iquety\Prospection\EventStore\Memory\MemoryQuery;
use Iquety\Prospection\EventStore\Memory\MemoryStore;
use Iquety\PubSub\Event\Serializer\JsonEventSerializer;
use Tests\TestCase;

class EventStoreCase extends TestCase
{
    public function setUp(): void
    {
        MemoryConnection::instance()->reset();
    }

    public function eventStoreFactory(): EventStore
    {
        return new EventStore(
            new MemoryQuery(),
            new MemoryStore(),
            new JsonEventSerializer()
        );
    }
}
