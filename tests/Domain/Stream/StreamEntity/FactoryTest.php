<?php

declare(strict_types=1);

namespace Tests\Domain\Stream\StreamEntity;

use ArrayObject;
use DateTime;
use DateTimeImmutable;
use DomainException;
use Iquety\Prospection\Domain\Core\IdentityObject;
use Tests\Domain\Core\Support\DummyEntity;
use Tests\Domain\Core\Support\DummyValue;
use Tests\Domain\Stream\Support\DummyStreamEntity;
use Tests\Domain\Stream\Support\DummyStreamEntityVisibility;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    /** @test */
    public function exactParams(): void
    {
        /** @var DummyStreamEntity */
        $object = DummyStreamEntity::factory($this->stateValues());

        $this->assertTrue($object->aggregateId()->equalTo(new IdentityObject('123456')));
        $this->assertEquals('Ricardo', $object->one());
        $this->assertEquals(30, $object->two());
        $this->assertEquals(5.5, $object->three());
        $this->assertInstanceOf(DateTimeImmutable::class, $object->four());
        $this->assertInstanceOf(ArrayObject::class, $object->five());
        $this->assertInstanceOf(DummyValue::class, $object->six());
        $this->assertInstanceOf(DummyEntity::class, $object->seven());
        $this->assertInstanceOf(DateTimeImmutable::class, $object->createdOn());
        $this->assertInstanceOf(DateTimeImmutable::class, $object->updatedOn());
    }

    /** @test */
    public function withOcurredOn(): void
    {
        $occurredOn = new DateTimeImmutable();

        $values = $this->stateValues();
        $values['occurredOn'] = $occurredOn;

        /** @var DummyStreamEntity */
        $object = DummyStreamEntity::factory($values);

        $this->assertTrue($object->aggregateId()->equalTo(new IdentityObject('123456')));
        $this->assertEquals('Ricardo', $object->one());
        $this->assertEquals(30, $object->two());
        $this->assertEquals(5.5, $object->three());
        $this->assertInstanceOf(DateTimeImmutable::class, $object->four());
        $this->assertInstanceOf(ArrayObject::class, $object->five());
        $this->assertInstanceOf(DummyValue::class, $object->six());
        $this->assertInstanceOf(DummyEntity::class, $object->seven());
        $this->assertEquals($occurredOn, $object->createdOn());
        $this->assertEquals($occurredOn, $object->updatedOn());
    }

    /** @test */
    public function withOcurredOnCustomPos(): void
    {
        $occurredOn = new DateTimeImmutable();

        $values = $this->stateValues();
        $values['occurredOn'] = $occurredOn;

        krsort($values);

        /** @var DummyStreamEntity */
        $object = DummyStreamEntity::factory($values);

        $this->assertTrue($object->aggregateId()->equalTo(new IdentityObject('123456')));
        $this->assertEquals('Ricardo', $object->one());
        $this->assertEquals(30, $object->two());
        $this->assertEquals(5.5, $object->three());
        $this->assertInstanceOf(DateTimeImmutable::class, $object->four());
        $this->assertInstanceOf(ArrayObject::class, $object->five());
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

        $values = $this->stateValues();
        $values['eight'] = 888; // valor não pertence ao estado

        DummyStreamEntity::factory($values);
    }

    /** @test */
    public function publicConstructorException(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage(
            "Constructors of objects of type 'StreamEntity' must be protected"
        );

        DummyStreamEntityVisibility::factory($this->stateValues());
    }
}
