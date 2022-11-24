<?php

declare(strict_types=1);

namespace Tests\Domain\Stream\StreamEntity;

use DateTime;
use DateTimeImmutable;
use DomainException;
use Iquety\Prospection\Domain\Core\IdentityObject;
use Tests\Domain\Core\Support\DummyEntity;
use Tests\Domain\Core\Support\DummyValue;
use Tests\Domain\Stream\Support\DummyStreamEntity;

class FactoryTest extends StreamEntityCase
{
    /** @test */
    public function exactParams(): void
    {
        /** @var DummyStreamEntity */
        $object = DummyStreamEntity::factory([
            'aggregateId' => new IdentityObject('123456'),
            'one' => 'Ricardo',
            'two' => 30,
            'three' => 5.5,
            'four' => $this->dummyDateTimeFactory(),
            'five' => $this->dummyDateTimeFactory('now', 'UTC', DateTime::class),
            'six' => new DummyValue('test1'),
            'seven' => new DummyEntity(new IdentityObject('111'), 'test2'),
        ]);

        $this->assertTrue($object->aggregateId()->equalTo(new IdentityObject('123456')));
        $this->assertEquals('Ricardo', $object->one());
        $this->assertEquals(30, $object->two());
        $this->assertEquals(5.5, $object->three());
        $this->assertInstanceOf(DateTimeImmutable::class, $object->four());
        $this->assertInstanceOf(DateTime::class, $object->five());
        $this->assertInstanceOf(DummyValue::class, $object->six());
        $this->assertInstanceOf(DummyEntity::class, $object->seven());
        $this->assertInstanceOf(DateTimeImmutable::class, $object->createdOn());
        $this->assertInstanceOf(DateTimeImmutable::class, $object->updatedOn());
    }

    /** @test */
    public function withOcurredOn(): void
    {
        $occurredOn = $this->dummyDateTimeFactory();

        /** @var DummyStreamEntity */
        $object = DummyStreamEntity::factory([
            'aggregateId' => new IdentityObject('123456'),
            'one' => 'Ricardo',
            'two' => 30,
            'three' => 5.5,
            'four' => $this->dummyDateTimeFactory(),
            'five' => $this->dummyDateTimeFactory('now', 'UTC', DateTime::class),
            'six' => new DummyValue('test1'),
            'seven' => new DummyEntity(new IdentityObject('111'), 'test2'),
            'occurredOn' => $occurredOn // campo especial para setagem da ocorrência do evento
        ]);

        $this->assertTrue($object->aggregateId()->equalTo(new IdentityObject('123456')));
        $this->assertEquals('Ricardo', $object->one());
        $this->assertEquals(30, $object->two());
        $this->assertEquals(5.5, $object->three());
        $this->assertInstanceOf(DateTimeImmutable::class, $object->four());
        $this->assertInstanceOf(DateTime::class, $object->five());
        $this->assertInstanceOf(DummyValue::class, $object->six());
        $this->assertInstanceOf(DummyEntity::class, $object->seven());
        $this->assertEquals($occurredOn, $object->createdOn());
        $this->assertEquals($occurredOn, $object->updatedOn());
    }

    /** @test */
    public function withOcurredOnCustomPos(): void
    {
        $occurredOn = $this->dummyDateTimeFactory();

        /** @var DummyStreamEntity */
        $object = DummyStreamEntity::factory([
            'aggregateId' => new IdentityObject('123456'),
            'one' => 'Ricardo',
            'two' => 30,
            'three' => 5.5,
            'occurredOn' => $occurredOn, // campo especial para setagem da ocorrência do evento
            'four' => $this->dummyDateTimeFactory(),
            'five' => $this->dummyDateTimeFactory('now', 'UTC', DateTime::class),
            'six' => new DummyValue('test1'),
            'seven' => new DummyEntity(new IdentityObject('111'), 'test2'),
        ]);

        $this->assertTrue($object->aggregateId()->equalTo(new IdentityObject('123456')));
        $this->assertEquals('Ricardo', $object->one());
        $this->assertEquals(30, $object->two());
        $this->assertEquals(5.5, $object->three());
        $this->assertInstanceOf(DateTimeImmutable::class, $object->four());
        $this->assertInstanceOf(DateTime::class, $object->five());
        $this->assertInstanceOf(DummyValue::class, $object->six());
        $this->assertInstanceOf(DummyEntity::class, $object->seven());
        $this->assertEquals($occurredOn, $object->createdOn());
        $this->assertEquals($occurredOn, $object->updatedOn());
    }

    /** @test */
    public function withExtraParams(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage("Unknown named parameter \$eight");

        DummyStreamEntity::factory([
            'aggregateId' => new IdentityObject('123456'),
            'one' => 'Ricardo',
            'two' => 30,
            'three' => 5.5,
            'four' => $this->dummyDateTimeFactory(),
            'five' => $this->dummyDateTimeFactory('now', 'UTC', DateTime::class),
            'six' => new DummyValue('test1'),
            'seven' => new DummyEntity(new IdentityObject('111'), 'test2'),
            'eight' => 888, // valor não pertence ao estado
        ]);
    }
}
