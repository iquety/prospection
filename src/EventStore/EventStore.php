<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore;

use DateTimeImmutable;
use Iquety\Prospection\Domain\AggregateRoot;
use Iquety\Prospection\Domain\DomainEvent;
use Iquety\PubSub\Event\Event;

interface EventStore
{
    public function store(AggregateRoot $aggregate, DomainEvent $event): void;

    /** @param array<Event> $variosEventos */
    public function storeMultiple(AggregateRoot $aggregate, array $domainEventList): void;

    public function countAll(): int;

    public function countAggregate(AggregateRoot $aggregate): int;

    public function streamSince(AggregateRoot $aggregate, StreamId $streamId): EventStream;

    public function streamFor(AggregateRoot $aggregate, string $aggregateId): EventStream;

    /** @return array<int,Descritor> */
    public function list(AggregateRoot $aggregate, Interval $interval): array;

    /** @return array<int,Descritor> */
    public function listConsolidated(AggregateRoot $aggregate, Interval $interval): array;

    /** @return array<int,Descritor> */
    public function listMaterialization(
        AggregateRoot $aggregate,
        DateTimeImmutable $initialMoment,
        Interval $interval
    ): array;

    public function remove(StreamId $fluxoId): void;

    public function removePrevious(StreamId $fluxoId): void;

    public function removeAll(): void;
}
