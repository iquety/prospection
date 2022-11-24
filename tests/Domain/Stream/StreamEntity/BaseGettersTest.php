<?php

declare(strict_types=1);

namespace Tests\Domain\Stream\StreamEntity;

use DateTime;
use DateTimeImmutable;
use Iquety\Prospection\Domain\Core\IdentityObject;
use Tests\Domain\Core\Support\DummyEntity;
use Tests\Domain\Core\Support\DummyValue;

class BaseGettersTest extends StreamEntityCase
{
    /** @test */
    public function stateGetters(): void
    {
        $occurredOn = '2022-10-10 10:10:10';

        $object = $this->dummyStreamEntityFactory($occurredOn);

        $this->assertTrue($object->aggregateId()->equalTo(new IdentityObject('123456')));
        $this->assertTrue($object->identity()->equalTo(new IdentityObject('123456')));
        $this->assertEquals('Ricardo', $object->one());
        $this->assertEquals(30, $object->two());
        $this->assertEquals(5.5, $object->three());
        $this->assertInstanceOf(DateTimeImmutable::class, $object->four());
        $this->assertInstanceOf(DateTime::class, $object->five());
        $this->assertTrue($object->six()->equalTo(new DummyValue('test1')));
        $this->assertTrue(
            $object->seven()->equalTo(new DummyEntity(new IdentityObject('111'), 'test2'))
        );

        $this->assertInstanceOf(DateTimeImmutable::class, $object->createdOn());
        $this->assertEquals(
            $this->dummyDateTimeFactory($occurredOn, "UTC"),
            $object->createdOn()
        );
        
        $this->assertInstanceOf(DateTimeImmutable::class, $object->updatedOn());
        $this->assertEquals(
            $this->dummyDateTimeFactory($occurredOn, "UTC", DateTime::class),
            $object->updatedOn()
        );
    }
}