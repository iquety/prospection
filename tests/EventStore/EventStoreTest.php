<?php

declare(strict_types=1);

namespace Tests\EventStore;

use InvalidArgumentException;
use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\Domain\Stream\DomainEvent;
use Iquety\Prospection\EventStore\EventSnapshot;
use Iquety\Prospection\EventStore\EventStore;
use Iquety\Prospection\EventStore\EventStream;
use Iquety\Prospection\EventStore\Query;
use Iquety\Prospection\EventStore\Store;
use Iquety\PubSub\Event\Serializer\EventSerializer;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use RuntimeException;
use Tests\Domain\Stream\Support\DummyStreamEntity;
use Tests\TestCase;

class EventStoreTest extends TestCase
{
    /** @test */
    public function storeMultipleException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("You must provide at least one event to store");

        /**
         * @var Query $query
         * @var Store $store
         * @var EventSerializer $serializer
         */
        $object = new EventStore(
            $this->createMock(Query::class),
            $this->createMock(Store::class),
            $this->createMock(EventSerializer::class)
        );

        $object->storeMultiple(DummyStreamEntity::class, []);
    }

    // /** @test */
    // public function storeAggregateIdException(): void
    // {
    //     $this->expectException(InvalidArgumentException::class);
    //     $this->expectExceptionMessage("You must provide at least one event to store");

    //     /** @var InvocationMocker */
    //     $store = $this->createMock(Store::class);
    //     $store->method('transaction')->will($this->returnCallback(function($closure) {
    //         $closure();
    //     }));

    //     /** @var Store $store */
    //     $object = new EventStore(
    //         $this->createMock(Query::class), // eventListForAggregate
    //         $store,
    //         $this->createMock(EventSerializer::class)
    //     );

    //     $state = $this->stateValues();
    //     $eventOne = new EventSnapshot($state);

    //     $state['aggregateId'] = new IdentityObject('7654321');
    //     $eventTwo = new EventSnapshot($state);

    //     /** 
    //      * @var DomainEvent $eventOne 
    //      * @var DomainEvent $eventTwo
    //      */
    //     $object->storeMultiple(DummyStreamEntity::class, [
    //         $eventOne,
    //         $eventTwo
    //     ]);
    // }

    // private function eventFactory(): DomainEvent
    // {
    //     /** @var InvocationMocker */
    //     $event = $this->createMock(DomainEvent::class);
    //     // $event->method('aggregateId')->willReturn('1234567');
    //     // $event->method('label')->willReturn('monomo.mono');

    //     /** @var DomainEvent $event */
    //     return $event;
    // }

    // /** @test */
    // public function differentAggregateEvents(): void
    // {
    //     $this->expectException(RuntimeException::class);
    //     $this->expectExceptionMessage("All events must belong to the same aggregate");
    //     $this->expectExceptionCode(100180);

    //     /** @var InvocationMocker */
    //     $query = $this->createMock(Query::class);
        
    //     $store = $this->createMock(Store::class);

    //     $serializer = $this->createMock(EventSerializer::class);

    //     /**
    //      * @var Query $query
    //      * @var Store $store
    //      * @var EventSerializer $serializer
    //      */
    //     $stream = new EventStore($query, $store, $serializer);
        
    //     // primeiro evento completo
    //     $stream->store(DummyStreamEntity::class, new EventSnapshot($this->stateValues()));

    //     // segundo evento
    //     $stream->store(DummyStreamEntity::class, new EventSnapshot(['one' => 'Pereira']));
        
    // }

    // /** @test */
    // public function storeEvent(): void
    // {
    //     /** @var InvocationMocker */
    //     $query = $this->createMock(Query::class);
        
    //     $store = $this->createMock(Store::class);

    //     $serializer = $this->createMock(EventSerializer::class);

    //     /**
    //      * @var Query $query
    //      * @var Store $store
    //      * @var EventSerializer $serializer
    //      */
    //     $stream = new EventStore(
    //         $query,
    //         $store,
    //         $serializer
    //     );
        
    //     // primeiro evento completo
    //     $stream->store(
    //         DummyStreamEntity::class,
    //         new EventSnapshot($this->stateValues())
    //     );

    //     // segundo evento
    //     $stream->store(
    //         DummyStreamEntity::class,
    //         new EventSnapshot(['one' => 'Pereira'])
    //     );

        
    //     // $stream->addEvent($this->eventFactory(), 1);
    //     // $stream->addEvent($this->eventFactory(), 2);
    //     // $stream->addEvent($this->eventFactory(), 3);

    //     // $this->assertCount(3, $stream->events());
    //     // $this->assertEquals(3, $stream->version());
    // }
}
