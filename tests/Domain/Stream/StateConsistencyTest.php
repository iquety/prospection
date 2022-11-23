<?php

declare(strict_types=1);

namespace Tests\Domain\Stream;

use DateTimeImmutable;
use DomainException;
use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\Domain\Stream\DomainEvent;
use Iquety\Prospection\Domain\Stream\State;
use Iquety\Prospection\EventStore\EventSnapshot;
use Tests\TestCase;

class StateConsistencyTest extends TestCase
{
    public function checkableMethodsProvider(): array
    {
        return [
            'toArray'     => [ 'toArray' ],
            'toSnapshot'  => [ 'toSnapshot' ],
            'createdOn'   => [ 'createdOn' ],
            'updatedOn'   => [ 'updatedOn' ],
            'aggregateId' => [ 'aggregateId' ],
        ];
    }

    /** 
     * @test 
     * @dataProvider checkableMethodsProvider
     */
    public function consistencyCheck(string $methodName): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage(
            "Aggregate status is incomplete or not yet committed. " .
            "The first event of an aggregate's flow must always provide the complete state."
        );

        $state = new State([
            'aggregateId',
            'name',
            'email'
        ]);

        $state->$methodName();
    }

    /** @test */
    public function incompleteStateOnFirstEvent(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage(
            "Aggregate status is incomplete or not yet committed. " .
            "The first event of an aggregate's flow must always provide the complete state."
        );

        /** @var InvocationMocker */
        $aggregateId = $this->createMock(IdentityObject::class);
        $aggregateId->method('value')->willReturn('1234567');
 
        /** @var InvocationMocker */
        $eventOne = $this->createMock(DomainEvent::class);
        $eventOne->method('occurredOn')->willReturn(new DateTimeImmutable());
        $eventOne->method('toArray')->willReturn([
            'aggregateId' => $aggregateId,
            'name'        => 'Ricardo',
            // 'email'       => 'contato@gmail.com'
        ]);

        $state = new State([
            'aggregateId',
            'name',
            'email' // primeiro evento não tem valor pra email
        ]);

        /** @var DomainEvent $eventOne */
        $state->change($eventOne);
    }
}
