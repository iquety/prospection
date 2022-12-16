<?php

declare(strict_types=1);

namespace Tests\EventStore;

use Iquety\Prospection\Domain\Stream\DomainEvent;
use Iquety\Prospection\EventStore\EventStream;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use RuntimeException;
use Tests\TestCase;

class EventStreamTest extends TestCase
{
    /** @test */
    public function addEvent(): void
    {
        $stream = new EventStream();

        $stream->addEvent($this->eventFactory(), 1);
        $stream->addEvent($this->eventFactory(), 2);
        $stream->addEvent($this->eventFactory(), 3);

        $this->assertCount(3, $stream->events());
        $this->assertEquals(3, $stream->version());
        $this->assertEquals(3, $stream->count());
    }

    /** @test */
    public function addEventsWithSameVersion(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("This event cannot be added because it is out of sync");

        $stream = new EventStream();
        $stream->addEvent($this->eventFactory(), 1);
        $stream->addEvent($this->eventFactory(), 1);
    }

    /** @test */
    public function addEventsWithInvertedVersion(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("This event cannot be added because it is out of sync");

        $stream = new EventStream();
        $stream->addEvent($this->eventFactory(), 2);
        $stream->addEvent($this->eventFactory(), 1);
    }

    private function eventFactory(): DomainEvent
    {
        /** @var InvocationMocker */
        $event = $this->createMock(DomainEvent::class);

        /** @var DomainEvent $event */
        return $event;
    }
}
