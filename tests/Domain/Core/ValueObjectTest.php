<?php

declare(strict_types=1);

namespace Tests\Domain\Core;

use Tests\Domain\Core\Support\DummyCompositeValue;
use Tests\Domain\Core\Support\DummyValue;
use Tests\Domain\Core\Support\DummyValueExtended;
use Tests\Domain\Core\Support\DummyValueReplica;
use Tests\TestCase;

class ValueObjectTest extends TestCase
{
    public function equalityProvider(): array
    {
        return [
            ['12345'],
            ['12.345']
        ];
    }

    /**
     * @test
     * @dataProvider equalityProvider
     */
    public function equality(mixed $value): void
    {
        $objectOne = new DummyValue($value);
        $objectTwo = new DummyValue($value);

        $this->assertNotSame($objectOne, $objectTwo);

        $this->assertTrue($objectOne->equalTo($objectTwo));
    }

    public function inequalityProvider(): array
    {
        return [
            ['12345', '12.345'],
            ['12.345', '12345'],
        ];
    }

    /**
     * @test
     * @dataProvider inequalityProvider
     */
    public function inequality(mixed $one, mixed $two): void
    {
        $objectOne = new DummyValue($one);
        $objectTwo = new DummyValue($two);

        $this->assertNotSame($objectOne, $objectTwo);

        $this->assertFalse($objectOne->equalTo($objectTwo));
    }

    /** @test */
    public function sameValueSameObject(): void
    {
        $objectOne = new DummyValue('test');
        $objectTwo = new DummyValue('test');

        $this->assertNotSame($objectOne, $objectTwo);

        $this->assertTrue($objectOne->equalTo($objectTwo));
    }

    /** @test */
    public function sameValueDifferentObjects(): void
    {
        $objectOne = new DummyValue('test');
        $objectTwo = new DummyValueReplica('test');

        $this->assertNotSame($objectOne, $objectTwo);

        $this->assertFalse($objectOne->equalTo($objectTwo));
    }

    /** @test */
    public function sameValueDifferentObjectsExtended(): void
    {
        $objectOne = new DummyValue('test');
        $objectTwo = new DummyValueExtended('test');

        $this->assertNotSame($objectOne, $objectTwo);

        $this->assertFalse($objectOne->equalTo($objectTwo));
    }

    /** @test */
    public function toArray(): void
    {
        $this->assertEquals(
            ['myValue' => 'test'],
            (new DummyValue('test'))->toArray()
        );
    }

    /** @test */
    public function compositeToArray(): void
    {
        $this->assertEquals(
            ['myValue' => 'test', 'other' => 'more'],
            (new DummyCompositeValue('test', 'more'))->toArray()
        );
    }

    /** @test */
    public function value(): void
    {
        $this->assertEquals('test', (new DummyValue('test'))->value());
    }

    /** @test */
    public function compositeValue(): void
    {
        $this->assertEquals(
            ['myValue' => 'test', 'other' => 'more'],
            (new DummyCompositeValue('test', 'more'))->value()
        );
    }

    /** @test */
    public function stringRepresentation(): void
    {
        $object = new DummyValue('test');

        $this->assertEquals("DummyValue [test]", (string)$object);
    }
}
