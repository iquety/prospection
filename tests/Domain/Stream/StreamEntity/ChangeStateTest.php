<?php

declare(strict_types=1);

namespace Tests\Domain\Stream\StreamEntity;

use DomainException;
use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\EventStore\EventSnapshot;
use Tests\Domain\Core\Support\DummyEntity;
use Tests\Domain\Core\Support\DummyValue;
use Tests\TestCase;

class ChangeStateTest extends TestCase
{
    public function changeStateProvider(): array
    {
        return [
            [ 'one', 'Ricardo', 'Pereira' ],
            [ 'two', 30, 25 ],
            [ 'three', 5.5, 1.5 ]
        ];
    }

    /**
     * @test
     * @dataProvider changeStateProvider
     */
    public function changeState(string $method, mixed $value, mixed $valueChanged): void
    {
        $object = $this->dummyStreamEntityFactory();

        $this->assertTrue($object->aggregateId()->equalTo(new IdentityObject('123456')));
        $this->assertEquals($value, $object->$method());

        $snapshot = new EventSnapshot([
            'aggregateId' => new IdentityObject('123456'),
            $method => $valueChanged
        ]);

        $object->changeState($snapshot);

        $this->assertCount(1, $object->changes());
        $this->assertEquals([ $snapshot ], $object->changes());
        $this->assertEquals($valueChanged, $object->$method());
    }

    /** @test */
    public function changeStateValueObject(): void
    {
        $object = $this->dummyStreamEntityFactory();

        $this->assertTrue($object->aggregateId()->equalTo(new IdentityObject('123456')));
        $this->assertEquals($object->one(), 'Ricardo');
        $this->assertTrue($object->six()->equalTo(new DummyValue('test1')));

        $object->changeState(new EventSnapshot([
            'aggregateId' => new IdentityObject('123456'),
            'six' => new DummyValue('test7')
        ]));

        $object->changeState(new EventSnapshot([
            'aggregateId' => new IdentityObject('123456'),
            'one' => 'Pereira'
        ]));

        $this->assertCount(2, $object->changes());
        $this->assertEquals($object->one(), 'Pereira');
        $this->assertTrue($object->six()->equalTo(new DummyValue('test7')));
    }

    /** @test */
    public function changeStateEntity(): void
    {
        $object = $this->dummyStreamEntityFactory();

        $this->assertTrue($object->aggregateId()->equalTo(new IdentityObject('123456')));
        $this->assertTrue(
            $object->seven()->equalTo(new DummyEntity(new IdentityObject('111'), 'test1'))
        );

        $object->changeState(new EventSnapshot([
            'aggregateId' => new IdentityObject('123456'),
            'seven' => new DummyEntity(new IdentityObject('111'), 'test7')
        ]));

        // igualdade por identidade
        $this->assertTrue(
            $object->seven()->equalTo(new DummyEntity(new IdentityObject('111'), 'test7'))
        );

        // igualdade por identidade
        $this->assertTrue(
            $object->seven()->equalTo(new DummyEntity(new IdentityObject('111'), 'test888'))
        );

        // mudança do valor
        $this->assertEquals('test7', $object->seven()->myValue());
    }

    /** @test */
    public function invalidAggregateId(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage(
            "The aggregation ID contained in the event does " .
            "not match the aggregation root ID"
        );

        $object = $this->dummyStreamEntityFactory();

        $this->assertTrue($object->aggregateId()->equalTo(new IdentityObject('123456')));

        $object->changeState(new EventSnapshot([
            'aggregateId' => new IdentityObject('123456888888'), // id diferente
            'seven' => new DummyEntity(new IdentityObject('111'), 'test7')
        ]));
    }
}
