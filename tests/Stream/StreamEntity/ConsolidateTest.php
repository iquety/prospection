<?php

declare(strict_types=1);

namespace Tests\Stream\StreamEntity;

use Iquety\Domain\Core\IdentityObject;
use Iquety\Domain\Event\DomainEvent;
use Iquety\Prospection\EventStore\EventSnapshot;
use Tests\TestCase;

class ConsolidateTest extends TestCase
{
    /** @test */
    public function consolidateOnly(): void
    {
        $object = $this->streamEntityFactory();

        $this->assertEquals('Ricardo', $object->one());
        $this->assertEquals(30, $object->two());
        $this->assertEquals(5.5, $object->three());
        $this->assertCount(0, $object->changes()); // nenhum mudaça

        // consolidar uma entidade, significa reaplicar todos os eventos já ocorridos
        // (e persistidos em algum banco de dados)
        // O estado de uma entidade é composto de todos os eventos ocorridos
        // desde que ela nasceu
        $object->consolidate([
            new EventSnapshot([
                'aggregateId' => new IdentityObject('123456'),
                'one' => 'Ronaldo',
            ]),
            new EventSnapshot([
                'aggregateId' => new IdentityObject('123456'),
                'two' => 11,
                'three' => 2.2,
            ]),
            new EventSnapshot([
                'aggregateId' => new IdentityObject('123456'),
                'three' => 5.1,
            ])
        ]);

        $this->assertEquals('Ronaldo', $object->one());
        $this->assertEquals(11, $object->two());
        $this->assertEquals(5.1, $object->three());

        // consolidar os eventos já ocorridos não mudam o estado "real",
        // mas apenas restabelece o estado atual
        $this->assertCount(0, $object->changes());
    }

    /** @test */
    public function consolidateAndChanges(): void
    {
        $object = $this->streamEntityFactory();

        $this->assertEquals('Ricardo', $object->one());
        $this->assertEquals(30, $object->two());
        $this->assertEquals(5.5, $object->three());
        $this->assertCount(0, $object->changes());

        // consolidar uma entidade, significa reaplicar todos os eventos já ocorridos
        // (e persistidos em algum banco de dados)
        // O estado de uma entidade é composto de todos os eventos ocorridos
        // desde que ela nasceu
        $object->consolidate([
            new EventSnapshot([
                'aggregateId' => new IdentityObject('123456'),
                'one' => 'Ronaldo',
            ]),
            new EventSnapshot([
                'aggregateId' => new IdentityObject('123456'),
                'two' => 11,
                'three' => 2.2,
            ])
        ]);

        $this->assertEquals('Ronaldo', $object->one());
        $this->assertEquals(11, $object->two());
        $this->assertEquals(2.2, $object->three());

        // consolidar os eventos já ocorridos não mudam o estado "real",
        // mas apenas restabelece o estado atual
        $this->assertCount(0, $object->changes());

        // muda o estado atual
        $object->changeState(new EventSnapshot([
            'aggregateId' => new IdentityObject('123456'),
            'one' => 'Roberto',
        ]));

        $this->assertEquals('Roberto', $object->one());
        $this->assertEquals(11, $object->two());
        $this->assertEquals(2.2, $object->three());

        // uma mudança de estado ocorreu no estado
        $this->assertCount(1, $object->changes());
        $this->assertInstanceOf(DomainEvent::class, $object->changes()[0]);

        $object->changeState(new EventSnapshot([
            'aggregateId' => new IdentityObject('123456'),
            'two' => 88,
        ]));

        // outra mudança de estado ocorreu no estado
        $this->assertCount(2, $object->changes());
        $this->assertInstanceOf(DomainEvent::class, $object->changes()[0]);
        $this->assertInstanceOf(DomainEvent::class, $object->changes()[1]);
    }

    /** @test */
    public function consolidateEmpty(): void
    {
        $object = $this->streamEntityFactory();

        $object->consolidate([]);

        $this->assertEquals('Ricardo', $object->one());
        $this->assertEquals(30, $object->two());
        $this->assertEquals(5.5, $object->three());
        $this->assertCount(0, $object->changes());
    }
}
