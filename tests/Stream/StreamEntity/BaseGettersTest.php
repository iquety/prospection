<?php

declare(strict_types=1);

namespace Tests\Stream\StreamEntity;

use ArrayObject;
use DateTime;
use DateTimeImmutable;
use Iquety\Domain\Core\IdentityObject;
use Tests\Stream\Support\DummyEntity;
use Tests\Stream\Support\DummyValue;
use Tests\TestCase;

class BaseGettersTest extends TestCase
{
    /** @test */
    public function stateGetters(): void
    {
        $occurredOn = '2022-10-10 10:10:10';

        $object = $this->streamEntityFactory($occurredOn);

        $this->assertTrue($object->aggregateId()->equalTo(new IdentityObject('123456')));
        $this->assertTrue($object->identity()->equalTo(new IdentityObject('123456')));
        $this->assertEquals('Ricardo', $object->one());
        $this->assertEquals(30, $object->two());
        $this->assertEquals(5.5, $object->three());
        $this->assertInstanceOf(DateTimeImmutable::class, $object->four());
        $this->assertInstanceOf(ArrayObject::class, $object->five());
        $this->assertTrue($object->six()->equalTo(new DummyValue('test1')));
        $this->assertTrue(
            $object->seven()->equalTo(new DummyEntity(new IdentityObject('111'), 'test2'))
        );

        $this->assertInstanceOf(DateTimeImmutable::class, $object->createdOn());
        $this->assertEquals(
            new DateTimeImmutable($occurredOn),
            $object->createdOn()
        );

        $this->assertInstanceOf(DateTimeImmutable::class, $object->updatedOn());
        $this->assertEquals(
            new DateTime($occurredOn),
            $object->updatedOn()
        );
    }
}
