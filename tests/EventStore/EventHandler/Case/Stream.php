<?php

declare(strict_types=1);

namespace Tests\EventStore\EventHandler\Case;

use InvalidArgumentException;
use Iquety\Domain\Core\IdentityObject;
use Iquety\Prospection\EventStore\EventSnapshot;
use Tests\EventStore\Support\DummyEntityOne;
use Tests\EventStore\Support\DummyEventOne;
use Tests\EventStore\Support\DummyEventTwo;

/**
 * @method array getPersistedEvents()
 * @method EventStore eventStoreFactory()
 * @method void resetDatabase()
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
trait Stream
{
    /** @test */
    public function streamFor(): void
    {
        $this->resetDatabase();

        $object = $this->eventStoreFactory();

        $object->registerEventType(DummyEventOne::class);
        $object->registerEventType(DummyEventTwo::class);

        /** @var EventSnapshot $one */
        $one = EventSnapshot::factory([
            'aggregateId' => new IdentityObject('12345'),
            'one' => 'Ricardo',
            'two' => 'Pereira',
        ]);

        /** @var DummyEventOne $two */
        $two = DummyEventOne::factory([
            'aggregateId' => new IdentityObject('12345'),
            'one' => 'Ricardo',
        ]);

        /** @var DummyEventTwo $thr */
        $thr = DummyEventTwo::factory([
            'aggregateId' => new IdentityObject('12345'),
            'two' => 'Ricardo',
        ]);

        $object->storeMultiple(DummyEntityOne::class, [
            $one,
            $two,
            $thr
        ]);

        $stream = $object->streamFor(DummyEntityOne::class, new IdentityObject('12345'));

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

    /** @test */
    public function streamForEmpty(): void
    {
        $object = $this->eventStoreFactory();

        $object->registerEventType(DummyEventOne::class);
        $object->registerEventType(DummyEventTwo::class);

        $stream = $object->streamFor(DummyEntityOne::class, new IdentityObject('77777'));

        $this->assertEquals(0, $stream->count());
        $this->assertEquals([], $stream->events());
    }

    /** @test */
    public function streamSince(): void
    {
        $this->resetDatabase();

        $object = $this->eventStoreFactory();

        $object->registerEventType(DummyEventOne::class);
        $object->registerEventType(DummyEventTwo::class);

        /** @var EventSnapshot $one */
        $one = EventSnapshot::factory([
            'aggregateId' => new IdentityObject('12345'),
            'one' => 'Ricardo',
            'two' => 'Pereira',
        ]);

        /** @var DummyEventOne $two */
        $two = DummyEventOne::factory([
            'aggregateId' => new IdentityObject('12345'),
            'one' => 'Ricardo',
        ]);

        /** @var DummyEventTwo $thr */
        $thr = DummyEventTwo::factory([
            'aggregateId' => new IdentityObject('12345'),
            'two' => 'Ricardo',
        ]);

        $object->storeMultiple(DummyEntityOne::class, [
            $one,
            $two,
            $thr
        ]);

        $this->assertEquals(
            1,
            $object->streamSince(DummyEntityOne::class, new IdentityObject('12345'), 3)->count()
        );
        $this->assertEquals(
            2,
            $object->streamSince(DummyEntityOne::class, new IdentityObject('12345'), 2)->count()
        );
        $this->assertEquals(
            3,
            $object->streamSince(DummyEntityOne::class, new IdentityObject('12345'), 1)->count()
        );
    }

    /** @test */
    public function streamSinceEmpty(): void
    {
        $object = $this->eventStoreFactory();

        $object->registerEventType(DummyEventOne::class);
        $object->registerEventType(DummyEventTwo::class);

        $this->assertEquals(
            0,
            $object->streamSince(DummyEntityOne::class, new IdentityObject('77777'), 3)->count()
        );
        $this->assertEquals(
            0,
            $object->streamSince(DummyEntityOne::class, new IdentityObject('77777'), 2)->count()
        );
        $this->assertEquals(
            0,
            $object->streamSince(DummyEntityOne::class, new IdentityObject('77777'), 1)->count()
        );
    }

    /** @test */
    public function streamSinceVersionException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Invalid version provided. Event versions always start with 1'
        );

        $object = $this->eventStoreFactory();

        $object->streamSince(DummyEntityOne::class, new IdentityObject('77777'), 0);
    }
}
