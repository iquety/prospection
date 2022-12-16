<?php

declare(strict_types=1);

namespace Tests\EventStore\EventHandler;

use Iquety\Prospection\EventStore\EventStore;
use Iquety\Prospection\EventStore\Memory\MemoryConnection;
use Iquety\Prospection\EventStore\Memory\MemoryQuery;
use Iquety\Prospection\EventStore\Memory\MemoryStore;
use Iquety\PubSub\Event\Serializer\JsonEventSerializer;
use Tests\EventStore\EventHandler\Case\AbstractCase;

class MemoryHandlerTest extends AbstractCase
{
    public function getPersistedEvents(): array
    {
        return MemoryConnection::instance()->all();
    }

    public function eventStoreFactory(): EventStore
    {
        return new EventStore(
            new MemoryQuery(),
            new MemoryStore(),
            new JsonEventSerializer()
        );
    }

    public function resetDatabase(): void
    {
        MemoryConnection::instance()->reset();
    }
}
