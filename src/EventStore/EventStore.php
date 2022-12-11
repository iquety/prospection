<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore;

use Closure;
use DateTimeImmutable;
use InvalidArgumentException;
use Iquety\Prospection\Domain\Stream\DomainEvent;
use Iquety\PubSub\Event\Serializer\EventSerializer;
use RuntimeException;
use Throwable;

class EventStore
{
    private const SNAPSHOT_SIZE = 10;

    /** @var array<string,string> */
    private array $eventRegisterList = [];

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

                $aggregateLabel = $aggregateSignature::label();

                // para identificar exceções não ligadas ao estado do evento
                $commonExceptionCode = 100180;

                $diffAggregateException = new RuntimeException(
                    "All events must belong to the same aggregate",
                    $commonExceptionCode
                );

                foreach ($domainEventList as $event) {
                    if (! $event instanceof DomainEvent){
                        throw new RuntimeException(
                            "Only domain events can be stored",
                            $commonExceptionCode
                        );
                    }

                    if ($aggregateId !== $event->aggregateId()->value()) {
                        throw $diffAggregateException;
                    }

                    if (
                        ! $event instanceof EventSnapshot
                        && $aggregateLabel !== $event::aggregateLabel()
                    ) {
                        throw $diffAggregateException;
                    }

                    $version = $version === 0
                        ? $this->query->nextVersion($aggregateLabel, $aggregateId)
                        : $version + 1;

                    $snapshot = (int)($version === 1);

                    $this->store->add(
                        $aggregateId,
                        $aggregateLabel,
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
                    . " of file " . $error->getFile(),
                    $error->getCode(),
                    $error
                );
            }
        });
    }

    public function countAll(): int
    {
        return $this->query->countEvents();
    }

    public function countAggregates(string $aggregateLabel): int
    {
        return $this->query->countAggregates($aggregateLabel);
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
        $aggregateEvents = $this->query->eventListForConsolidation(
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
        $aggregateEvents = $this->query->eventListForConsolidation(
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

            $groupedEventList[$aggregateId][] = $this->serializer->unserialize($event['eventData']);
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

    public function registerEventType(string $eventSignature): void
    {
        $this->eventRegisterList[$eventSignature::label()] = $eventSignature;
    }
    
    public function remove(StreamId $streamId): void
    {
        $this->store->remove($streamId->aggregateLabel(), $streamId->aggregateId(), $streamId->version());
    }

    public function removePrevious(StreamId $streamId): void
    {
        $this->store->removePrevious($streamId->aggregateLabel(), $streamId->aggregateId(), $streamId->version());
    }

    public function removeAll(): void
    {
        $this->store->removeAll();
    }

    /** @param array<array<string,mixed>> $eventList */
    private function streamFactory(array $eventList): EventStream
    {
        $stream = new EventStream();

        foreach ($eventList as $event) {
            $state = $this->serializer->unserialize($event['eventData']);
            $domainEvent = $this->eventFactory($event['eventLabel'], $state);

            $stream->addEvent($domainEvent, (int)$event['version']);
        }

        return $stream;
    }

    private function eventFactory(string $eventLabel, array $state): DomainEvent
    {
        $factory = EventSnapshot::class;

        if (isset($this->eventRegisterList[$eventLabel]) === true) {
            $factory = $this->eventRegisterList[$eventLabel];
        }

        return call_user_func([$factory, "factory"], $state);
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
            $this->query->nextVersion($aggregateSignature::label(), $aggregateId),
            1,
            $this->serializer->serialize($event->toArray()),
            new DateTimeImmutable()
        );
    }
}
