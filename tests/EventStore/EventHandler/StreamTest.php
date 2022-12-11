<?php

declare(strict_types=1);

namespace Tests\EventStore\EventHandler;

use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\EventStore\EventSnapshot;
use Tests\EventStore\Support\DummyEntityOne;
use Tests\EventStore\Support\DummyEventOne;
use Tests\EventStore\Support\DummyEventTwo;

class StreamTest extends EventStoreCase
{
    /** @test */
    public function streamFor(): void
    {
        $object = $this->eventStoreFactory();

        $object->registerEventType(DummyEventOne::class);
        $object->registerEventType(DummyEventTwo::class);

        $one = EventSnapshot::factory([ 
            'aggregateId' => new IdentityObject('12345'),
            'one' => 'Ricardo',
            'two' => 'Pereira',
        ]);

        $two = DummyEventOne::factory([ 
            'aggregateId' => new IdentityObject('12345'),
            'one' => 'Ricardo',
        ]);

        $thr = DummyEventTwo::factory([ 
            'aggregateId' => new IdentityObject('12345'),
            'two' => 'Ricardo',
        ]);

        $object->storeMultiple(DummyEntityOne::class, [
            $one,
            $two,
            $thr
        ]);

        $stream = $object->streamFor(DummyEntityOne::class, '12345');

        $this->assertEquals(3, $stream->count());

        $this->assertInstanceOf(EventSnapshot::class, $stream->events()[0]);
        $this->assertTrue($one->equalTo($stream->events()[0]));
        $this->assertEquals(
            $one->occurredOn()->format('Y-m-d H:i:s.u'),
            $stream->events()[0]->occurredOn()->format('Y-m-d H:i:s.u')
        );

        $this->assertInstanceOf(DummyEventOne::class, $stream->events()[1]);
        $this->assertTrue($two->equalTo($stream->events()[1]));
        $this->assertEquals($two, $stream->events()[1]);
        $this->assertEquals(
            $two->occurredOn()->format('Y-m-d H:i:s.u'),
            $stream->events()[1]->occurredOn()->format('Y-m-d H:i:s.u')
        );

        $this->assertInstanceOf(DummyEventTwo::class, $stream->events()[2]);
        $this->assertTrue($thr->equalTo($stream->events()[2]));
        $this->assertEquals($thr, $stream->events()[2]);
        $this->assertEquals(
            $thr->occurredOn()->format('Y-m-d H:i:s.u'),
            $stream->events()[2]->occurredOn()->format('Y-m-d H:i:s.u')
        );
    }
}
