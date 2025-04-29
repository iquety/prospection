<?php

declare(strict_types=1);

namespace Tests\Event;

use ArrayObject;
use Iquety\Prospection\Domain\Entity;
use Iquety\Prospection\Domain\IdentityObject;
use Iquety\Prospection\Domain\ValueObject;
use Iquety\Prospection\Domain\ValueParser;
use stdClass;
use Tests\Domain\Support\DummyEntity;
use Tests\Domain\Support\DummyEntityExtended;
use Tests\Domain\Support\DummyValue;
use Tests\Domain\Support\DummyValueExtended;
use Tests\TestCase;

class ValueParserTest extends TestCase
{
    public function primitiveProvider(): array
    {
        return [
            'string'  => [ 'teste' ],
            'integer' => [ 12345 ],
            'integer 1' => [ 1 ],
            'integer 0' => [ 0 ],
            'double'  => [ 123.45 ],
            'double 1.0'  => [ 1.0 ],
            'double 0.0'  => [ 0.0 ],
            'array'   => [ [1, 2, 3] ],
            'empty array'   => [ [] ],
            'boolean true' => [ true ],
            'boolean false' => [ false ],
        ];
    }

    public function objectProvider(): array
    {
        return [
            ArrayObject::class  => [ new ArrayObject() ],
            stdClass::class => [ new stdClass() ],
            'simple object' => [ (object)[] ],
        ];
    }

    public function valueObjectProvider(): array
    {
        return [
            DummyValue::class  => [ new DummyValue('ricardo') ],
            DummyValueExtended::class => [ new DummyValueExtended('ricardo') ],
            'anonimous class' => [ new class() extends ValueObject { 
                private string $name = 'ricardo'; 
            } ],
        ];
    }

    public function entityProvider(): array
    {
        return [
            DummyEntity::class  => [ new DummyEntity(new IdentityObject('abc'), 'ricardo') ],
            DummyEntityExtended::class => [ new DummyEntityExtended(new IdentityObject('abc'), 'ricardo') ],
            'anonimous class' => [ new class() extends Entity { 
                private string $name = 'ricardo'; 
                public function identity(): IdentityObject { return new IdentityObject('abc'); }
            } ],
        ];
    }

    /**
     * @test
     * @dataProvider entityProvider
     */
    public function isEntity(mixed $value): void
    {
        $parser = new ValueParser($value);

        $this->assertTrue($parser->isEntity());
    }

    /**
     * @test
     * @dataProvider objectProvider
     * @dataProvider primitiveProvider
     * @dataProvider valueObjectProvider
     */
    public function isNotEntity(mixed $value): void
    {
        $parser = new ValueParser($value);

        $this->assertFalse($parser->isEntity());
    }

    /**
     * @test
     * @dataProvider primitiveProvider
     */
    public function isPrimitive(mixed $value): void
    {
        $parser = new ValueParser($value);

        $this->assertTrue($parser->isPrimitive());
    }

    /**
     * @test
     * @dataProvider entityProvider
     * @dataProvider objectProvider
     * @dataProvider valueObjectProvider
     */
    public function isNotPrimitive(mixed $value): void
    {
        $parser = new ValueParser($value);

        $this->assertFalse($parser->isPrimitive());
    }

    /**
     * @test
     * @dataProvider objectProvider
     */
    public function isObject(mixed $value): void
    {
        $parser = new ValueParser($value);

        $this->assertTrue($parser->isObject());
    }

    /**
     * @test
     * @dataProvider primitiveProvider
     */
    public function isNotObject(mixed $value): void
    {
        $parser = new ValueParser($value);

        $this->assertFalse($parser->isObject());
    }

    /**
     * @test
     * @dataProvider valueObjectProvider
     */
    public function isValueObject(mixed $value): void
    {
        $parser = new ValueParser($value);

        $this->assertTrue($parser->isValueObject());
    }

    /**
     * @test
     * @dataProvider objectProvider
     * @dataProvider primitiveProvider
     * @dataProvider entityProvider
     */
    public function isNotValueObject(mixed $value): void
    {
        $parser = new ValueParser($value);

        $this->assertFalse($parser->isValueObject());
    }
}