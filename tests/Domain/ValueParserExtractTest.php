<?php

declare(strict_types=1);

namespace Tests\Event;

use ArrayObject;
use DateTimeImmutable;
use Iquety\Prospection\Domain\Entity;
use Iquety\Prospection\Domain\IdentityObject;
use Iquety\Prospection\Domain\ValueObject;
use Iquety\Prospection\Domain\ValueParser;
use RuntimeException;
use stdClass;
use Tests\TestCase;

class ValueParserExtractTest extends TestCase
{
    public function statelessProvider(): array
    {
        return [
            ArrayObject::class  => [ new ArrayObject() ],
            stdClass::class => [ new stdClass() ],
            'simple object' => [ (object)[] ],
        ];
    }

    /** 
     * @test
     * @dataProvider statelessProvider
     */
    public function invalidState(mixed $value): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Only stateful or DateTimeImmutable values can be converted to primitives'
        );

        $parser = new ValueParser($value);

        $this->assertTrue($parser->toPrimitives());
    }

    /** @test */
    public function convertArray(): void
    {
        $value = new class('ricardo') extends ValueObject {
            public function __construct(private string $name){}
        };
        
        $valueOne = [ 'name' => $value ];

        $this->assertSame(
            [ 'name' => 'ricardo' ],
            (new ValueParser($valueOne))->toPrimitives()
        );
    }

    /** @test */
    public function convertArrayDate(): void
    {
        $date = new DateTimeImmutable();

        $valueOne = [ 'created_on' => $date ];

        $this->assertSame(
            [ 'created_on' => $date->format('Y-m-d H:i:s.u') ],
            (new ValueParser($valueOne))->toPrimitives()
        );
    }

    /** @test */
    public function noConvertArrayLevelOne(): void
    {
        $valueOne = [ 'name' => 'ricardo' ];

        $this->assertSame(
            [ 'name' => 'ricardo' ],
            (new ValueParser($valueOne))->toPrimitives()
        );
    }

    /** @test */
    public function noConvertArrayLevelTwo(): void
    {
        $valueOne = [ 
            'name' => 'ricardo',
            'age' => 43,
            'range' => [ 'start' => 10, 'end' => 50 ]
        ];

        $this->assertSame(
            [ 
                'name' => 'ricardo',
                'age' => 43,
                'range' => [ 'start' => 10, 'end' => 50 ]
            ],
            (new ValueParser($valueOne))->toPrimitives()
        );
    }

    /** @test */
    public function noConvertArrayLevelThree(): void
    {
        $valueOne = [ 
            'name' => 'ricardo',
            'age' => [ 'age' => 43, 'range' => [ 'start' => 10, 'end' => 50 ] ]
        ];

        $this->assertSame(
            [ 
                'name' => 'ricardo',
                'age' => [ 'age' => 43, 'range' => [ 'start' => 10, 'end' => 50 ] ]
            ],
            (new ValueParser($valueOne))->toPrimitives()
        );
    }

    /** @test */
    public function convertValueLevelOne(): void
    {
        $valueOne = new class('ricardo') extends ValueObject {
            public function __construct(private string $name){}
        };

        $this->assertSame(
            [ 'name' => 'ricardo' ],
            (new ValueParser($valueOne))->toPrimitives()
        );
    }

    /** @test */
    public function convertValueLevelTwo(): void
    {
        $valueTwoAge = new class(43) extends ValueObject {
            public function __construct(private int $age){}
        };

        $valueTwoRange = new class(10, 50) extends ValueObject {
            public function __construct(private int $start, private int $end){}
        };

        $valueOne = new class('ricardo', $valueTwoAge, $valueTwoRange) extends ValueObject {
            public function __construct(
                private string $name,
                private ValueObject $age,
                private ValueObject $range,
            ){}
        };

        $this->assertSame(
            [ 
                'name' => 'ricardo',
                'age' => 43,
                'range' => [ 'start' => 10, 'end' => 50 ]
            ],
            (new ValueParser($valueOne))->toPrimitives()
        );
    }

    /** @test */
    public function convertValueLevelThree(): void
    {
        $valueThree = new class(10, 50) extends ValueObject {
            public function __construct(private int $start, private int $end){}
        };

        $valueTwo = new class(43, $valueThree) extends ValueObject {
            public function __construct(private int $age, private ValueObject $range){}
        };

        $valueOne = new class('ricardo', $valueTwo) extends ValueObject {
            public function __construct(
                private string $name,
                private ValueObject $age
            ){}
        };

        $this->assertSame(
            [ 
                'name' => 'ricardo',
                'age' => [ 'age' => 43, 'range' => [ 'start' => 10, 'end' => 50 ] ]
            ],
            (new ValueParser($valueOne))->toPrimitives()
        );
    }

    /** @test */
    public function convertEntityLevelOne(): void
    {
        $identity = new IdentityObject('abcdefghij');

        $entityOne = new class($identity, 'ricardo') extends Entity {
            public function __construct(private IdentityObject $identity, private string $name){}
            public function identity(): IdentityObject { return $this->identity; }
        };

        $this->assertSame([ 
                'identity' => 'abcdefghij',
                'name' => 'ricardo',
            ],
            (new ValueParser($entityOne))->toPrimitives()
        );
    }

    /** @test */
    public function convertEntityLevelTwo(): void
    {
        $entityTwoAge = new class(new IdentityObject('ghijkl'), 43) extends Entity {
            public function __construct(
                private IdentityObject $identity,
                private int $age
            ){}
            public function identity(): IdentityObject { return $this->identity; }
        };

        $entityTwoRange = new class(new IdentityObject('mnopqr'), 10, 50) extends Entity {
            public function __construct(
                private IdentityObject $identity,
                private int $start,
                private int $end
            ){}
            public function identity(): IdentityObject { return $this->identity; }
        };

        $entityOne = new class(new IdentityObject('abcdef'), 'ricardo', $entityTwoAge, $entityTwoRange) extends Entity {
            public function __construct(
                private IdentityObject $identity,
                private string $name,
                private Entity $age,
                private Entity $range
            ){}
            public function identity(): IdentityObject { return $this->identity; }
        };

        $this->assertSame(
            [ 
                'identity' => 'abcdef',
                'name' => 'ricardo',
                'age' => [ 'identity' => 'ghijkl', 'age' => 43 ],
                'range' => [ 'identity' => 'mnopqr', 'start' => 10, 'end' => 50 ]
            ],
            (new ValueParser($entityOne))->toPrimitives()
        );
    }

    /** @test */
    public function convertEntityLevelThree(): void
    {
        $entityThree = new class(new IdentityObject('ghijkl'), 43) extends Entity {
            public function __construct(
                private IdentityObject $identity,
                private int $age
            ){}
            public function identity(): IdentityObject { return $this->identity; }
        };


        $entityTwo = new class(new IdentityObject('mnopqr'), $entityThree, 10, 50) extends Entity {
            public function __construct(
                private IdentityObject $identity,
                private Entity $age,
                private int $start,
                private int $end
            ){}
            public function identity(): IdentityObject { return $this->identity; }
        };

        $entityOne = new class(new IdentityObject('abcdef'), 'ricardo', $entityTwo) extends Entity {
            public function __construct(
                private IdentityObject $identity,
                private string $name,
                private Entity $range
            ){}
            public function identity(): IdentityObject { return $this->identity; }
        };

        $this->assertSame(
            [ 
                'identity' => 'abcdef',
                'name' => 'ricardo',
                'range' => [
                    'identity' => 'mnopqr',
                    'age' => [ 'identity' => 'ghijkl', 'age' => 43 ],
                    'start' => 10,
                    'end' => 50
                ]
            ],
            (new ValueParser($entityOne))->toPrimitives()
        );
    }
}