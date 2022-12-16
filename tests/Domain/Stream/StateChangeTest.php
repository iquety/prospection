<?php

declare(strict_types=1);

namespace Tests\Domain\Stream;

use DateTimeImmutable;
use DateTimeZone;
use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\Domain\Stream\DomainEvent;
use Iquety\Prospection\Domain\Stream\State;
use Iquety\Prospection\EventStore\EventSnapshot;
use Tests\TestCase;

class StateChangeTest extends TestCase
{
    /** @test */
    public function changeState(): void
    {
        /** @var InvocationMocker */
        $aggregateId = $this->createMock(IdentityObject::class);
        $aggregateId->method('value')->willReturn('1234567');

        $eventOccurence = new DateTimeImmutable("now", new DateTimeZone("UTC"));

        $event = new EventSnapshot([
            'aggregateId' => $aggregateId,
            'name'        => 'Ricardo',
            'email'       => 'contato@gmail.com',
            'occurredOn'  => $eventOccurence
        ]);

        $state = new State([
            'aggregateId',
            'name',
            'email'
        ]);

        $state->change($event); // seta o createdOn e UpdatedOn

        $this->assertEquals('1234567', $state->aggregateId()->value());
        $this->assertEquals('Ricardo', $state->value('name'));
        $this->assertEquals('contato@gmail.com', $state->value('email'));
        $this->assertInstanceOf(DateTimeImmutable::class, $state->createdOn());
        $this->assertEquals($eventOccurence, $state->createdOn());
        $this->assertInstanceOf(DateTimeImmutable::class, $state->updatedOn());
        $this->assertEquals($eventOccurence, $state->updatedOn());

        $values = $state->toArray();

        $this->assertEquals('1234567', $values['aggregateId']->value());
        $this->assertEquals('Ricardo', $values['name']);
        $this->assertEquals('contato@gmail.com', $values['email']);
        $this->assertInstanceOf(DateTimeImmutable::class, $values['createdOn']);
        $this->assertEquals($eventOccurence, $values['createdOn']);
        $this->assertInstanceOf(DateTimeImmutable::class, $values['updatedOn']);
        $this->assertEquals($eventOccurence, $values['updatedOn']);
    }

    /** @test */
    public function changeStateSequence(): void
    {
        $state = new State([
            'aggregateId',
            'name',
            'email'
        ]);

        /** @var InvocationMocker */
        $aggregateId = $this->createMock(IdentityObject::class);
        $aggregateId->method('value')->willReturn('1234567');

        // - - - - - - - - - - - -

        /** @var InvocationMocker */
        $eventOne = $this->createMock(DomainEvent::class);
        $eventOne->method('aggregateId')->willReturn($aggregateId);
        $eventOne->method('occurredOn')->willReturn(new DateTimeImmutable());
        $eventOne->method('toArray')->willReturn([
            'aggregateId' => $aggregateId,
            'name'        => 'Ricardo',
            'email'       => 'contato@gmail.com'
        ]);

        /** @var DomainEvent $eventOne */
        $state->change($eventOne);

        $values = $state->toArray();

        $this->assertEquals('1234567', $values['aggregateId']->value());
        $this->assertEquals('Ricardo', $values['name']);
        $this->assertEquals('contato@gmail.com', $values['email']);
        $this->assertInstanceOf(DateTimeImmutable::class, $values['createdOn']);
        $this->assertInstanceOf(DateTimeImmutable::class, $values['updatedOn']);

        // - - - - - - - - - - - -

        /** @var InvocationMocker */
        $eventTwo = $this->createMock(DomainEvent::class);
        $eventTwo->method('aggregateId')->willReturn($aggregateId);
        $eventTwo->method('occurredOn')->willReturn(new DateTimeImmutable());
        $eventTwo->method('toArray')->willReturn([
            'aggregateId' => $aggregateId,
            'name'        => 'Pereira',
        ]);

        $previousValues = $state->toArray();

        /** @var DomainEvent $eventTwo */
        $state->change($eventTwo);

        $values = $state->toArray();

        $this->assertEquals($previousValues['aggregateId'], $values['aggregateId']);
        $this->assertNotEquals($previousValues['name'], $values['name']);  // mudou
        $this->assertEquals($previousValues['email'], $values['email']);
        $this->assertEquals($previousValues['createdOn'], $values['createdOn']);
        $this->assertNotEquals($previousValues['updatedOn'], $values['updatedOn']); // mudou

        // - - - - - - - - - - - -

        /** @var InvocationMocker */
        $eventThree = $this->createMock(DomainEvent::class);
        $eventThree->method('aggregateId')->willReturn($aggregateId);
        $eventThree->method('occurredOn')->willReturn(new DateTimeImmutable());
        $eventThree->method('toArray')->willReturn([
            'aggregateId' => $aggregateId,
            'email'       => 'other@gmail.com'
        ]);

        $previousValues = $state->toArray();

        /** @var DomainEvent $eventThree */
        $state->change($eventThree);

        $values = $state->toArray();

        $this->assertEquals($previousValues['aggregateId'], $values['aggregateId']);
        $this->assertEquals($previousValues['name'], $values['name']);
        $this->assertNotEquals($previousValues['email'], $values['email']); // mudou
        $this->assertEquals($previousValues['createdOn'], $values['createdOn']);
        $this->assertNotEquals($previousValues['updatedOn'], $values['updatedOn']); // mudou
    }
}
