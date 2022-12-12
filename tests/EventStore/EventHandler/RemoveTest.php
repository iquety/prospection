<?php

declare(strict_types=1);

namespace Tests\EventStore\EventHandler;

use DateTimeImmutable;
use InvalidArgumentException;
use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\EventStore\EventSnapshot;
use Iquety\Prospection\EventStore\Memory\MemoryConnection;
use Iquety\Prospection\EventStore\StreamId;
use Tests\EventStore\Support\DummyEntityOne;
use Tests\EventStore\Support\DummyEntityThr;
use Tests\EventStore\Support\DummyEntityTwo;
use Tests\EventStore\Support\DummyEventOne;
use Tests\EventStore\Support\DummyEventTwo;

class RemoveTest extends EventHandlerCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->databaseFactory();
    }

    /** @test */
    public function removeAll(): void
    {
        $object = $this->eventStoreFactory();

        $this->assertEquals(5, $object->countAll());

        $object->removeAll();

        $this->assertEquals(0, $object->countAll());
    }

    /** @test */
    public function remove(): void
    {
        $object = $this->eventStoreFactory();

        $stream = $object->streamFor(DummyEntityThr::class, new IdentityObject('67890'));
        $this->assertEquals(5, $object->countAll());
        $this->assertEquals(2, $stream->count());
        
        $object->remove(DummyEntityThr::class, new IdentityObject('67890'), 1);

        $stream = $object->streamFor(DummyEntityThr::class, new IdentityObject('67890'));
        $this->assertEquals(4, $object->countAll());
        $this->assertEquals(1, $stream->count());
    }

    /** @test */
    public function removePrevious(): void
    {
        $object = $this->eventStoreFactory();

        $stream = $object->streamFor(DummyEntityThr::class, new IdentityObject('67890'));
        $this->assertEquals(5, $object->countAll());
        $this->assertEquals(2, $stream->count());
        
        $object->removePrevious(DummyEntityThr::class, new IdentityObject('67890'), 2);

        $stream = $object->streamFor(DummyEntityThr::class, new IdentityObject('67890'));
        $this->assertEquals(4, $object->countAll());
        $this->assertEquals(1, $stream->count());
    }

    /** @test */
    public function removePreviousSupVersion(): void
    {
        $object = $this->eventStoreFactory();

        $stream = $object->streamFor(DummyEntityThr::class, new IdentityObject('67890'));
        $this->assertEquals(5, $object->countAll());
        $this->assertEquals(2, $stream->count());
        
        // existe mapenas 2 eventos
        // removendo anteriores a 3
        $object->removePrevious(DummyEntityThr::class, new IdentityObject('67890'), 3);

        $stream = $object->streamFor(DummyEntityThr::class, new IdentityObject('67890'));
        $this->assertEquals(3, $object->countAll());
        $this->assertEquals(0, $stream->count());
    }
}
