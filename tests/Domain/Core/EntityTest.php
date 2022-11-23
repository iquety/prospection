<?php

declare(strict_types=1);

namespace Tests\Domain\Core;

use Iquety\Prospection\Domain\Core\IdentityObject;
use Tests\Domain\Core\Support\DummyEntity;
use Tests\Domain\Core\Support\DummyEntityExtended;
use Tests\Domain\Core\Support\DummyEntityReplica;
use Tests\TestCase;

class EntityTest extends TestCase
{
    /** @test */
    public function equalityByIdentity(): void
    {
        $objectOne = new DummyEntity(new IdentityObject('1111'), 'one');
        $objectTwo = new DummyEntity(new IdentityObject('1111'), 'two');

        $this->assertNotSame($objectOne, $objectTwo);

        $this->assertTrue($objectOne->equalTo($objectTwo));
    }

    /** @test */
    public function inequalityByIdentity(): void
    {
        $objectOne = new DummyEntity(new IdentityObject('1111'), 'one');
        $objectTwo = new DummyEntity(new IdentityObject('1112'), 'one');

        $this->assertNotSame($objectOne, $objectTwo);

        $this->assertFalse($objectOne->equalTo($objectTwo));
    }

    /** @test */
    public function equalIdentityDifferentObjects(): void
    {
        $objectOne = new DummyEntity(new IdentityObject('1111'), 'one');
        $objectTwo = new DummyEntityReplica(new IdentityObject('1111'), 'one');

        $this->assertNotSame($objectOne, $objectTwo);

        $this->assertTrue($objectOne->equalTo($objectTwo));
    }

    /** @test */
    public function equalIdentityDifferentAbstractions(): void
    {
        $objectOne = new DummyEntity(new IdentityObject('1111'), 'one');
        $objectTwo = new DummyEntityExtended(new IdentityObject('1111'), 'one');

        $this->assertNotSame($objectOne, $objectTwo);

        $this->assertTrue($objectOne->equalTo($objectTwo));
    }

    /** @test */
    public function toArray(): void
    {
        $this->assertEquals(
            ['identity' => new IdentityObject('1111'), 'myValue' => 'one'],
            (new DummyEntity(new IdentityObject('1111'), 'one'))->toArray()
        );
    }

    /** @test */
    public function stringRepresentation(): void
    {
        $object = new DummyEntity(new IdentityObject('1111'), 'one');

        $this->assertEquals("DummyEntity [\n" . 
            "    identity = IdentityObject [1111]\n" . 
            "    myValue = one\n" . 
        "]", (string)$object);
    }
}
