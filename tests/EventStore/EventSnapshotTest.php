<?php

declare(strict_types=1);

namespace Tests\EventStore;

use ArrayObject;
use BadMethodCallException;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use Iquety\Domain\Core\IdentityObject;
use Iquety\Prospection\EventStore\EventSnapshot;
use Tests\TestCase;

class EventSnapshotTest extends TestCase
{
    /** @test */
    public function withoutAggregateId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("An event must have the value 'aggregateId'");

        new EventSnapshot([]);
    }

    /** @test */
    public function invalidAggregateId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf("The value 'aggregateId' must be of type %s", IdentityObject::class)
        );

        new EventSnapshot([
            'aggregateId' => new ArrayObject([])
        ]);
    }

    /** @test */
    public function construction(): void
    {
        /** @var InvocationMocker */
        $aggregateId = $this->createMock(IdentityObject::class);
        $aggregateId->method('value')->willReturn('1234567');

        $event = new EventSnapshot([
            'aggregateId' => $aggregateId,
            'other' => 123
        ]);

        $this->assertEquals('snapshot', $event->label());
        $this->assertInstanceOf(IdentityObject::class, $event->aggregateId());
        $this->assertEquals($event->aggregateId(), $event->toArray()['aggregateId']);
        $this->assertEquals(123, $event->toArray()['other']);
        $this->assertInstanceOf(DateTimeImmutable::class, $event->toArray()['occurredOn']);
        $this->assertTrue(
            new DateTimeImmutable("now", new DateTimeZone("UTC")) > $event->occurredOn()
        );
    }

    /** @test */
    public function withOccurrenceDate(): void
    {
        /** @var InvocationMocker */
        $aggregateId = $this->createMock(IdentityObject::class);
        $aggregateId->method('value')->willReturn('1234567');

        $occurrenceDate = new DateTimeImmutable("now", new DateTimeZone("UTC"));

        $event = new EventSnapshot([
            'aggregateId' => $aggregateId,
            'other' => 123,
            'occurredOn' => $occurrenceDate
        ]);

        $this->assertEquals('snapshot', $event->label());
        $this->assertInstanceOf(IdentityObject::class, $event->aggregateId());
        $this->assertEquals($event->aggregateId(), $event->toArray()['aggregateId']);
        $this->assertEquals(123, $event->toArray()['other']);
        $this->assertEquals($occurrenceDate, $event->toArray()['occurredOn']);
        $this->assertEquals($occurrenceDate, $event->occurredOn());
    }

    /** @test */
    public function aggregateLabel(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage(
            "Snapshots do not have labels as their aggregates are dynamic"
        );

        /** @var InvocationMocker */
        $aggregateId = $this->createMock(IdentityObject::class);
        $aggregateId->method('value')->willReturn('1234567');

        $event = new EventSnapshot([
            'aggregateId' => $aggregateId
        ]);

        $event->aggregateLabel();
    }
}
