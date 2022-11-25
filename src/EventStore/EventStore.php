<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore;

use DateTimeImmutable;
use InvalidArgumentException;
use Iquety\Prospection\Domain\Stream\DomainEvent;
use Iquety\PubSub\Event\Serializer\EventSerializer;
use RuntimeException;
use Throwable;

class EventStore
{
    private const SNAPSHOT_SIZE = 10;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Promoção de propriedades ainda não é suportada no PHPMD
     */
    public function __construct(
        private Query $query,
        private Store $store,
        private EventSerializer $serializer,
    ) {
    }

    public function store(string $aggregateSignature, DomainEvent $event): void
    {
        $this->storeMultiple($aggregateSignature, [ $event ]);
    }

    /** @param array<DomainEvent> $domainEventList */
    public function storeMultiple(string $aggregateSignature, array $domainEventList): void
    {
        if ($domainEventList === []) {
            throw new InvalidArgumentException(
                "You must provide at least one event to store"
            );
        }

        $aggregateId = $domainEventList[0]->aggregateId()->value();

        $this->store->transaction(function () use ($aggregateSignature, $aggregateId, $domainEventList) {
            try {
                $version = 0;

                foreach ($domainEventList as $event) {
                    if ($aggregateId !== $event->aggregateId()->value()
                    || $aggregateSignature::label() !== $event::aggregateLabel()
                    ) {
                        throw new RuntimeException(
                            "All events must belong to the same aggregate",
                            100180
                        );
                    }

                    $version = $version === 0
                        ? $this->query->nextVersion($aggregateId)
                        : $version + 1;

                    $snapshot = (int)($version === 1);

                    $this->store->add(
                        $aggregateId,
                        $event::aggregateLabel(),
                        $event::label(),
                        $version,
                        $snapshot,
                        $this->serializer->serialize($event->toArray()),
                        $event->occurredOn()
                    );

                    $createSnapshot = ($version % self::SNAPSHOT_SIZE) === 0;
                    if ($createSnapshot === true) {
                        $version++;
                        $this->storeSnapshot($aggregateSignature, $aggregateId);
                    }
                }
            } catch (Throwable $error) {
                throw new RuntimeException(
                    $this->makeStateErrorMessage($error)
                    . " in line " . $error->getLine() 
                    . " of file " . $error->getFile()
                );
            }
        });
    }

    private function makeStateErrorMessage(Throwable $exception): string
    {
        if ($exception->getCode() === 100180) {
            return $exception->getMessage();
        }

        return sprintf(
            "It may be that the aggregate state is incomplete. Erro: %s",
            $exception->getMessage()
        );
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    private function storeSnapshot(string $aggregateSignature, string $aggregateId): void
    {
        $eventList = $this->streamFor($aggregateSignature, $aggregateId)->events();
        $stateValues = $eventList[0]->toArray();

        /** @var StreamEntity $aggregate */
        $aggregate = $aggregateSignature::factory($stateValues);
        $aggregate->consolidate($eventList);

        $event = $aggregate->toSnapshot();

        $this->store->add(
            $aggregateId,
            $aggregateSignature::label(),
            $event::label(),
            $this->query->nextVersion($aggregateId),
            1,
            $this->serializer->serialize($event->toArray()),
            new DateTimeImmutable()
        );
    }

    public function countAll(): int
    {
        return $this->query->countEvents();
    }

    public function countAggregate(string $aggregateLabel): int
    {
        return $this->query->countAggregateEvents($aggregateLabel);
    }

    public function streamSince(string $aggregateSignature, StreamId $streamId): EventStream
    {
        $eventList = $this->query->eventListForVersion(
            $aggregateSignature::label(),
            $streamId->aggregateId()->value(),
            $streamId->version()
        );

        return $this->streamFactory($eventList);
    }

    public function streamFor(string $aggregateSignature, string $aggregateId): EventStream
    {
        $eventList = $this->query->eventListForAggregate($aggregateSignature::label(), $aggregateId);

        return $this->streamFactory($eventList);
    }

    /** @param array<array<string,mixed>> $eventList */
    private function streamFactory(array $eventList): EventStream
    {
        $stream = new EventStream();

        foreach ($eventList as $event) {
            $domainEvent = new EventSnapshot(
                $this->serializer->unserialize($event['data'])
            );

            $stream->addEvent($domainEvent, (int)$event['version']);
        }

        return $stream;
    }

    /** @return array<int,Descriptor> */
    public function list(string $aggregateSignature, Interval $interval): array
    {
        $aggregateList = $this->query->aggregateList($aggregateSignature::label(), $interval);

        $list = [];

        foreach ($aggregateList as $register) {
            /** @var EventSnapshot $snapshot */
            $snapshot = $this->serializer->unserialize($register['data']);

            $list[] = new Descriptor($aggregateSignature, $snapshot);
        }

        return $list;
    }

    /** @return array<int,Descritor> */
    public function listConsolidated(string $aggregateSignature, Interval $interval): array
    {
        $aggregateEvents = $this->query->eventListForRegisters(
            $this->query->aggregateList($aggregateSignature::label(), $interval)
        );

        $groupedByAggregate = [];
        foreach ($aggregateEvents as $event) {
            $aggregateId = $event['aggregate_id'];

            if (isset($groupedByAggregate[$aggregateId]) === false) {
                $groupedByAggregate[$aggregateId] = [];
            }

            $groupedByAggregate[$aggregateId][] = $this->serializer->unserialize($event['data']);
        }

        $list = [];

        foreach ($groupedByAggregate as $aggregateId => $eventList) {
            /** @var AggregateRoot $entity */
            $entity = new $aggregateSignature();

            foreach ($eventList as $evento) {
                $entity->consolidate([ $evento ]);
            }

            $list[] = new Descriptor($aggregateSignature, new EventSnapshot($entity->toArray()));
        }

        return $list;
    }

    /** @return array<int,Descritor> */
    public function listMaterialization(
        string $aggregateSignature,
        DateTimeImmutable $initialMoment,
        Interval $interval
    ): array {
        $aggregateEvents = $this->query->eventListForRegisters(
            $this->query->aggregateListByDate($aggregateSignature::label(), $initialMoment, $interval)
        );

        $groupedEventList = [];
        $groupedOccurrenceList = [];

        foreach ($aggregateEvents as $event) {
            $aggregateId = $event['aggregate_id'];

            if (isset($groupedEventList[$aggregateId]) === false) {
                $groupedOccurrenceList[$aggregateId] = [];
                $groupedEventList[$aggregateId] = [];
            }

            $groupedOccurrenceList[$aggregateId][] = new DateTimeImmutable($event['occurredOn']);

            $groupedEventList[$aggregateId][] = $this->serializer->unserialize($event['data']);
        }

        $list = [];

        foreach ($groupedEventList as $aggregateId => $eventList) {
            /** @var AggregateRoot $entity */
            $entity = new $aggregateSignature();

            $entity->estado()->setarDataCriacao($groupedOccurrenceList[$aggregateId][0]);

            foreach ($eventList as $index => $event) {
                $entity->consolidate([ $event ]);
                $entity->estado()->setarDataAlteracao($groupedOccurrenceList[$aggregateId][$index]);
            }

            $list[] = new Descriptor($aggregateSignature, new EventSnapshot($entity->toArray()));
        }

        return $list;
    }

    public function remove(StreamId $streamId): void
    {
        $this->store->remove($streamId->aggregateId(), $streamId->version());
    }

    public function removePrevious(StreamId $streamId): void
    {
        $this->store->removePrevious($streamId->aggregateId(), $streamId->version());
    }

    public function removeAll(): void
    {
        $this->store->removeAll();
    }
}
