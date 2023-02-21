<?php

declare(strict_types=1);

namespace Tests\EventStore;

use ArrayObject;
use DateTimeImmutable;
use Iquety\Domain\Core\IdentityObject;
use Iquety\Prospection\EventStore\Descriptor;
use Iquety\Prospection\EventStore\EventSnapshot;
use Tests\Stream\Support\DummyEntity;
use Tests\Stream\Support\DummyValue;
use Tests\Stream\Support\DummyStreamEntity;
use Tests\TestCase;

class DescriptorTest extends TestCase
{
    public function stateValues(): array
    {
        return [
            'aggregateId' => new IdentityObject('123456'),
            'one'         => 'Ricardo',
            'two'         => 30,
            'three'       => 5.5,
            'four'        => new DateTimeImmutable('2022-10-10 10:10:10.703961'),
            'five'        => new ArrayObject(),
            'six'         => new DummyValue('test1'),
            'seven'       => new DummyEntity(new IdentityObject('111'), 'test2'),
        ];
    }

    /** @test */
    public function getters(): void
    {
        $occurredOn = new DateTimeImmutable("2022-10-10 10:10:10.703961");

        $stateValues = $this->stateValues();
        $stateValues['occurredOn'] = $occurredOn;

        $eventSnapshot = new EventSnapshot($stateValues);

        $descriptor = new Descriptor(
            DummyStreamEntity::class,
            $eventSnapshot,
            new DateTimeImmutable("2022-10-10 10:10:10.703961"),
            new DateTimeImmutable("2022-10-10 11:10:10.703961")
        );

        $this->assertEquals(
            new DateTimeImmutable("2022-10-10 10:10:10.703961"),
            $descriptor->createdOn()
        );
    }

    /** @test */
    public function toArray(): void
    {
        $occurredOn = new DateTimeImmutable("2022-10-10 10:10:10.703961");

        $stateValues = $this->stateValues();
        $stateValues['occurredOn'] = $occurredOn;

        $eventSnapshot = new EventSnapshot($stateValues);

        $descriptor = new Descriptor(
            DummyStreamEntity::class,
            $eventSnapshot,
            new DateTimeImmutable("2022-10-10 10:10:10.703961"),
            new DateTimeImmutable("2022-10-10 11:10:10.703961")
        );

        $this->assertEquals($stateValues, $descriptor->toArray());
    }

    /** @test */
    public function toAggregate(): void
    {
        $occurredOn = new DateTimeImmutable("2022-10-10 10:10:10.703961");

        $stateValues = $this->stateValues();
        $stateValues['occurredOn'] = $occurredOn;

        $eventSnapshot = new EventSnapshot($stateValues);

        $descriptor = new Descriptor(
            DummyStreamEntity::class,
            $eventSnapshot,
            new DateTimeImmutable("2022-10-10 10:10:10.703961"),
            new DateTimeImmutable("2022-10-10 11:10:10.703961")
        );

        $this->assertInstanceOf(DummyStreamEntity::class, $descriptor->toAggregate());
    }

    /** @test */
    public function toStringRepresentation(): void
    {
        $occurredOn = new DateTimeImmutable("2022-10-10 10:10:10.703961");

        $stateValues = $this->stateValues();
        $stateValues['occurredOn'] = $occurredOn;

        $eventSnapshot = new EventSnapshot($stateValues);

        $object = new Descriptor(
            DummyStreamEntity::class,
            $eventSnapshot,
            new DateTimeImmutable("2022-10-10 10:10:10.703961"),
            new DateTimeImmutable("2022-10-10 11:10:10.703961")
        );

        $this->assertEquals("DummyStreamEntity [\n" .
            "    aggregateId = IdentityObject [123456]\n" .
            "    one = Ricardo\n" .
            "    two = 30\n" .
            "    three = 5.5\n" .
            "    four = DateTimeImmutable UTC [2022-10-10 10:10:10.703961]\n" .
            "    five = ArrayObject()\n" .
            "    six = DummyValue [test1]\n" .
            "    seven = DummyEntity [...]\n" .
            "    createdOn = DateTimeImmutable UTC [2022-10-10 10:10:10.703961]\n" .
            "    updatedOn = DateTimeImmutable UTC [2022-10-10 10:10:10.703961]\n" .
            "    occurredOn = DateTimeImmutable UTC [2022-10-10 10:10:10.703961]\n" .
        "]", (string)$object);
    }
}
