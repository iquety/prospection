<?php

declare(strict_types=1);

namespace Tests\Domain\Core;

use Iquety\Prospection\Domain\Core\IdentityObject;
use Tests\TestCase;

class IdentityObjectTest extends TestCase
{
    /** @test */
    public function valueAlwaysAsString(): void
    {
        $this->assertIsString((new IdentityObject(123456))->value());
        $this->assertIsString((new IdentityObject('123.456.789'))->value());
        $this->assertIsString((new IdentityObject(123.45))->value());
    }

    public function equalityProvider(): array
    {
        return [
            ['12345', '12345'],
            ['12345', 12345],
            [12345, '12345'],
            [12345, 12345],

            ['12.345', '12.345'],
            ['12.345', 12.345],
            [12.345, '12.345'],
            [12.345, 12.345],
        ];
    }

    /**
     * @test
     * @dataProvider equalityProvider
     */
    public function equality(mixed $one, mixed $two): void
    {
        $objectOne = new IdentityObject($one);
        $objectTwo = new IdentityObject($two);

        $this->assertNotSame($objectOne, $objectTwo);

        $this->assertTrue($objectOne->equalTo($objectTwo));
    }

    public function inequalityProvider(): array
    {
        return [
            ['12345', '67890'],
            ['12345', 67890],
            [12345, '67890'],
            [12345, 67890],

            ['12.345', '67.890'],
            ['12.345', 67.890],
            [12.345, '67.890'],
            [12.345, 67.890],
        ];
    }

    /**
     * @test
     * @dataProvider inequalityProvider
     */
    public function inequality(mixed $one, mixed $two): void
    {
        $objectOne = new IdentityObject($one);
        $objectTwo = new IdentityObject($two);

        $this->assertNotSame($objectOne, $objectTwo);

        $this->assertFalse($objectOne->equalTo($objectTwo));
    }

    /** @test */
    public function stringRepresentation(): void
    {
        $object = new IdentityObject(123456);

        $this->assertEquals("IdentityObject [123456]", (string)$object);
    }
}
