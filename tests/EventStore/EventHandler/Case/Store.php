<?php

declare(strict_types=1);

namespace Tests\EventStore\EventHandler\Case;

use DateTimeImmutable;
use InvalidArgumentException;
use Iquety\Domain\Core\IdentityObject;
use Iquety\Prospection\EventStore\EventSnapshot;
use RuntimeException;
use Tests\EventStore\Support\DummyEntityOne;
use Tests\EventStore\Support\DummyEventCommon;
use Tests\EventStore\Support\DummyEventOne;
use Tests\EventStore\Support\DummyEventThr;
use Tests\EventStore\Support\DummyEventTwo;

/**
 * @method array getPersistedEvents()
 * @method EventStore eventStoreFactory()
 * @method void resetDatabase()
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
trait Store
{
    /** @test */
    public function emptyEventListException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("You must provide at least one event to store");

        $object = $this->eventStoreFactory();

        $object->storeMultiple(DummyEntityOne::class, []);
    }

    /** @test */
    public function onlyDomainEventException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Only domain events can be stored");

        $object = $this->eventStoreFactory();

        $object->storeMultiple(DummyEntityOne::class, [
            DummyEventCommon::factory([ // não é um DomainEvent
                'aggregateId' => new IdentityObject('12345'),
                'one' => 'Ricardo',
                'two' => 'Pereira'
            ])
        ]);
    }

    /** @test */
    public function aggregateIdException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("All events must belong to the same aggregate");

        $object = $this->eventStoreFactory();

        $object->storeMultiple(DummyEntityOne::class, [
            DummyEventOne::factory([
                'aggregateId' => new IdentityObject('12345'),
                'one' => 'Ricardo'
            ]),
            DummyEventOne::factory([
                'aggregateId' => new IdentityObject('67890'),
                'one' => 'Pereira'
            ]),
        ]);
    }

    /** @test */
    public function aggregateLabelException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("All events must belong to the same aggregate");

        $object = $this->eventStoreFactory();

        $object->storeMultiple(DummyEntityOne::class, [
            // aggregateLabel = aggregate.one
            DummyEventOne::factory([
                'aggregateId' => new IdentityObject('12345'),
                'one' => 'Ricardo',
            ]),
            // aggregateLabel = aggregate.two
            DummyEventThr::factory([
                'aggregateId' => new IdentityObject('12345'),
                'one' => 'Ricardo',
                'two' => 'Pereira'
            ])
        ]);
    }

    /** @test */
    public function storeVersioning(): void
    {
        $this->resetDatabase();

        $object = $this->eventStoreFactory();

        $one = EventSnapshot::factory([
            'aggregateId' => new IdentityObject('12345'),
            'one' => 'Ricardo',
            'two' => 'Pereira',
            'thr' => 'Dias',
        ]);

        $two = DummyEventOne::factory([
            'aggregateId' => new IdentityObject('12345'),
            'one' => 'Ricardo',
        ]);

        $thr = DummyEventTwo::factory([
            'aggregateId' => new IdentityObject('12345'),
            'two' => 'Ricardo',
        ]);

        $object->storeMultiple(DummyEntityOne::class, [ $one, $two, $thr ]);

        $list = $this->getPersistedEvents();

        // one - - - - - -
        $this->assertEquals(1, $list[0]['version']);
        $this->assertEquals($one->occurredOn()->format('Y-m-d H:i:s.u'), $list[0]['occurredOn']);
        $this->assertEquals(
            $one->occurredOn()->format('Y-m-d H:i:s.u'),
            json_decode($list[0]['eventData'])->occurredOn->date
        );

        // two - - - - - -
        $this->assertEquals(2, $list[1]['version']);
        $this->assertEquals(
            $two->occurredOn()->format('Y-m-d H:i:s.u'),
            $list[1]['occurredOn']
        );
        $this->assertEquals(
            $two->occurredOn()->format('Y-m-d H:i:s.u'),
            json_decode($list[1]['eventData'])->occurredOn->date
        );

        // thr - - - - - -
        $this->assertEquals(3, $list[2]['version']);
        $this->assertEquals($thr->occurredOn()->format('Y-m-d H:i:s.u'), $list[2]['occurredOn']);
        $this->assertEquals(
            $thr->occurredOn()->format('Y-m-d H:i:s.u'),
            json_decode($list[2]['eventData'])->occurredOn->date
        );
    }

    /** @test */
    public function storeSnapshot(): void
    {
        $this->resetDatabase();

        $object = $this->eventStoreFactory();

        $first = EventSnapshot::factory([
            'aggregateId' => new IdentityObject('12345'),
            'one' => 'Ricardo',
            'two' => 'Pereira',
            'thr' => 'Dias',
        ]);

        $others = DummyEventOne::factory([
            'aggregateId' => new IdentityObject('12345'),
            'one' => 'Ricardo',
        ]);

        $eventList = [ $first, ... array_fill(0, 14, $others) ];

        $this->assertCount(15, $eventList);

        $object->storeMultiple(DummyEntityOne::class, $eventList);

        $storedList = $this->getPersistedEvents();

        $this->assertCount(16, $storedList);

        // first snapshot - - - - - -
        $this->assertEquals(1, $storedList[0]['version']);
        $this->assertEquals(
            $first->occurredOn()->format('Y-m-d H:i:s.u'),
            $storedList[0]['occurredOn']
        );
        $this->assertEquals(
            $first->occurredOn()->format('Y-m-d H:i:s.u'),
            json_decode($storedList[0]['eventData'])->occurredOn->date
        );

        foreach (range(2, 10) as $version) {
            $index = $version - 1;
            $this->assertEquals($version, $storedList[$index]['version']);
        }

        // new snapshot - - - - - -
        $this->assertEquals(11, $storedList[10]['version']);
        $this->assertEquals('snapshot', $storedList[10]['eventLabel']);
        $this->assertEquals(1, $storedList[10]['snapshot']);
        $this->assertEquals(
            $first->occurredOn()->format('Y-m-d H:i:s'),
            (new DateTimeImmutable($storedList[10]['occurredOn']))->format('Y-m-d H:i:s')
        );
        $this->assertEquals(
            $first->occurredOn()->format('Y-m-d H:i:s'),
            preg_replace('/\..*/', '', json_decode($storedList[10]['eventData'])->occurredOn->date)
        );

        foreach (range(12, 16) as $version) {
            $index = $version - 1;
            $this->assertEquals($version, $storedList[$index]['version']);
        }
    }

    /** @test */
    public function storeSnapshotException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches(
            '/It may be that the aggregate state is incomplete. Erro: .*/'
        );

        $object = $this->eventStoreFactory();

        $first = EventSnapshot::factory([
            'aggregateId' => new IdentityObject('12345'),
            'one' => 'Ricardo',
            'two' => 'Pereira',
            'thr' => 'Dias',
            'fou' => 'Extra'
        ]);

        $others = DummyEventOne::factory([
            'aggregateId' => new IdentityObject('12345'),
            'one' => 'Ricardo',
        ]);

        $eventList = [ $first, ... array_fill(0, 14, $others) ];

        $this->assertCount(15, $eventList);

        $object->storeMultiple(DummyEntityOne::class, $eventList);
    }

    /** @test */
    public function storeOne(): void
    {
        $this->resetDatabase();

        $object = $this->eventStoreFactory();

        $event = EventSnapshot::factory([
            'aggregateId' => new IdentityObject('12345'),
            'one' => 'Ricardo',
            'two' => 'Pereira',
            'thr' => 'Dias',
        ]);

        $object->store(DummyEntityOne::class, $event);

        $list = $this->getPersistedEvents();

        $this->assertEquals(1, $list[0]['version']);
        $this->assertEquals(
            $event->occurredOn()->format('Y-m-d H:i:s.u'),
            $list[0]['occurredOn']
        );
        $this->assertEquals(
            $event->occurredOn()->format('Y-m-d H:i:s.u'),
            json_decode($list[0]['eventData'])->occurredOn->date
        );
    }
}
