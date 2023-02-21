<?php

declare(strict_types=1);

namespace Tests\EventStore\EventHandler\Case;

use Iquety\Domain\Core\IdentityObject;
use Iquety\Prospection\EventStore\EventStore;
use Tests\EventStore\Support\DummyEntityOne;

/**
 * @method array getPersistedEvents()
 * @method EventStore eventStoreFactory()
 * @method void resetDatabase()
 */
trait Remove
{
    /** @test */
    public function removeAll(): void
    {
        $object = $this->eventStoreFactory();

        $this->assertEquals(19, $object->countAllEvents());

        $object->removeAll();

        $this->assertEquals(0, $object->countAllEvents());
    }

    /** @test */
    public function remove(): void
    {
        $object = $this->eventStoreFactory();

        $stream = $object->streamFor(DummyEntityOne::class, new IdentityObject('12345'));
        $this->assertEquals(19, $object->countAllEvents());
        $this->assertEquals(3, $stream->count());

        $object->remove(DummyEntityOne::class, new IdentityObject('12345'), 1);

        $stream = $object->streamFor(DummyEntityOne::class, new IdentityObject('12345'));
        $this->assertEquals(18, $object->countAllEvents());
        $this->assertEquals(2, $stream->count());
    }

    /** @test */
    public function removePrevious(): void
    {
        $object = $this->eventStoreFactory();

        $stream = $object->streamFor(DummyEntityOne::class, new IdentityObject('12345'));
        $this->assertEquals(19, $object->countAllEvents());
        $this->assertEquals(3, $stream->count());

        $object->removePrevious(DummyEntityOne::class, new IdentityObject('12345'), 3);

        $stream = $object->streamFor(DummyEntityOne::class, new IdentityObject('12345'));
        $this->assertEquals(17, $object->countAllEvents());
        $this->assertEquals(1, $stream->count());
    }

    /** @test */
    public function removePreviousSupVersion(): void
    {
        $object = $this->eventStoreFactory();

        $stream = $object->streamFor(DummyEntityOne::class, new IdentityObject('12345'));
        $this->assertEquals(19, $object->countAllEvents());
        $this->assertEquals(3, $stream->count());

        // existe mapenas 3 eventos
        // removendo anteriores a 4
        $object->removePrevious(DummyEntityOne::class, new IdentityObject('12345'), 4);

        $stream = $object->streamFor(DummyEntityone::class, new IdentityObject('12345'));
        $this->assertEquals(16, $object->countAllEvents());
        $this->assertEquals(0, $stream->count());
    }
}
