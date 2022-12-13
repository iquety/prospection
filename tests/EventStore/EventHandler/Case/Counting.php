<?php

declare(strict_types=1);

namespace Tests\EventStore\EventHandler\Case;

use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\EventStore\EventSnapshot;
use Iquety\Prospection\EventStore\EventStore;
use Tests\EventStore\Support\DummyEntityOne;
use Tests\EventStore\Support\DummyEntityThr;
use Tests\EventStore\Support\DummyEntityTwo;

/**
 * @method array getPersistedEvents()
 * @method EventStore eventStoreFactory()
 * @method void resetDatabase()
 */
trait Counting
{
    /** @test */
    public function countAllEvents(): void
    {
        $this->assertEquals(19, $this->eventStoreFactory()->countAllEvents());
    }

    /** @test */
    public function countAggregateEvents(): void
    {
        $object = $this->eventStoreFactory();

        $this->eventStoreFactory()->storeMultiple(DummyEntityOne::class, [
            EventSnapshot::factory([
                'aggregateId' => new IdentityObject('77777'),
                'one' => 'Fulano',
                'two' => 'Ciclano',
                'thr' => 'Naitis'
            ]),
        ]);

        $count = $object->countAggregateEvents(DummyEntityOne::class, new IdentityObject('77777'));
        $this->assertEquals(1, $count);

        $count = $object->countAggregateEvents(DummyEntityOne::class, new IdentityObject('12345'));
        $this->assertEquals(3, $count);

        $count = $object->countAggregateEvents(DummyEntityOne::class, new IdentityObject('67890'));
        $this->assertEquals(3, $count);

        $count = $object->countAggregateEvents(DummyEntityTwo::class, new IdentityObject('abcde'));
        $this->assertEquals(2, $count);
    }

    /** @test */
    public function countAggregates(): void
    {
        $object = $this->eventStoreFactory();

        $this->assertEquals(5, $object->countAggregates(DummyEntityOne::class));
        $this->assertEquals(2, $object->countAggregates(DummyEntityTwo::class));
    }
}
