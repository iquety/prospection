<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore;

use DateTimeImmutable;
use InvalidArgumentException;
use Iquety\Prospection\Domain\AggregateRoot;
use Iquety\Prospection\Domain\DomainEvent;
use Iquety\Prospection\EventStore\Connection\MysqlConnection;
use Iquety\Prospection\EventStore\Persistence\MysqlPersistence;
use Iquety\Prospection\EventStore\Persistence\Persistence;
use Iquety\Prospection\EventStore\Query\MysqlQuery;
use Iquety\Prospection\EventStore\Query\Query;
use Iquety\PubSub\Event\Serializer\EventSerializer;
use RuntimeException;
use Throwable;

class MysqlEventStore implements EventStore
{
    private const SNAPSHOT_SIZE = 10;

    private Query $query;

    private Persistence $persistence;

    private EventSerializer $serializer;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Promoção de propriedades ainda não é suportada no PHPMD
     */
    public function __construct(
        MysqlConnection $connection,
        EventSerializer $serializer,
        string $eventTable = 'events'
    ) {
        $this->query       = new MysqlQuery($connection, $eventTable);
        $this->persistence = new MysqlPersistence($connection, $eventTable);
        $this->serializer  = $serializer;
    }

    public function store(AggregateRoot $aggregate, DomainEvent $event): void
    {
        $this->storeMultiple($aggregate, [ $event ]);
    }

    /** @param array<DomainEvent> $domainEventList */
    public function storeMultiple(AggregateRoot $aggregate, array $domainEventList): void
    {
        if ($domainEventList === []) {
            throw new InvalidArgumentException(
                "You must provide at least one event to store"
            );
        }

        $aggregateId = $domainEventList[0]->aggregateId()->value();

        $this->registrador->transacao(function () use ($aggregate, $aggregateId, $domainEventList) {
            try {
                $version = 0;

                foreach ($domainEventList as $event) {
                    if ($aggregateId !== $event->aggregateId()->value()) {
                        throw new RuntimeException(
                            "All events must belong to the same aggregate",
                            100180
                        );
                    }

                    $version = $version === 0
                        ? $this->query->nextVersion($aggregateId)
                        : $version + 1;

                    $snapshot = (int)($version === 1);

                    $this->persistence->add(
                        $aggregateId,
                        $aggregate::label(),
                        $event::label(),
                        $version,
                        $snapshot,
                        $this->serializer->serialize($event->toArray()),
                        $event->occurredOn()
                    );

                    $createSnapshot = ($version % self::SNAPSHOT_SIZE) === 0;
                    if ($createSnapshot === true) {
                        $version++;
                        $this->storeSnapshot($aggregate, $aggregateId);
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
    private function storeSnapshot(AggregateRoot $aggregate, string $aggregateId): void
    {
        /** @var $agregado EntidadeRaiz */
        $aggregate->consolidate($this->streamFor($aggregate, $aggregateId)->events());

        $event = $aggregate->state()->asSnapshot();

        $this->persistence->add(
            $aggregateId,
            $aggregate::label(),
            $event::label(),
            $this->query->nextVersion($aggregateId),
            1,
            $this->serializer->serialize($event),
            new DateTimeImmutable()
        );
    }

    public function countAll(): int
    {
        return $this->query->countEvents();
    }

    public function countAggregate(AggregateRoot $aggregate): int
    {
        return $this->query->countAggregateEvents($aggregate::label());
    }

    public function streamSince(AggregateRoot $aggregate, StreamId $streamId): EventStream
    {
        $eventList = $this->query->eventListForVersion(
            $streamId->aggregateId()->value(),
            $streamId->version()
        );

    return $this->streamFactory($aggregate, $eventList);
    }

    public function streamFor(AggregateRoot $aggregate, string $aggregateId): EventStream
    {
        $eventList = $this->query->eventListForAggregate($aggregateId);

        return $this->streamFactory($aggregate, $eventList);
    }

    /** @param array<array<string,mixed>> $eventList */
    private function streamFactory(AggregateRoot $aggregate, array $eventList): EventStream
    {
        $stream = new EventStream();

        foreach ($eventList as $event) {
            $domainEvent = $this->serializer->unserialize($event['data']);
            // TODO
            $domainEvent = new EventSnapshot([]);
            $stream->addEvent($domainEvent, (int)$event['version']);
        }

        return $stream;
    }

    /** @return array<int,Descriptor> */
    public function list(AggregateRoot $aggregate, Interval $interval): array
    {
        $aggregateList = $this->query->aggregateList($aggregate::label(), $interval);

        $list = [];

        foreach ($aggregateList as $register) {
            /** @var EventSnapshot $snapshot */
            $snapshot = $this->serializer->unserialize($register['data']);

            $list[] = new Descriptor($aggregate, $snapshot);
        }

        return $list;
    }

    /** @return array<int,Descritor> */
    public function listConsolidated(AggregateRoot $aggregate, Interval $interval): array
    {
        $aggregateEvents = $this->query->eventListForRegisters(
            $this->query->aggregateList($aggregate::label(), $interval)
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
            $aggregateClass = $aggregate::class;

            /** @var AggregateRoot $entity */
            $entity = new $aggregateClass();

            foreach ($eventList as $evento) {
                $entity->consolidate([ $evento ]);
            }

            $list[] = new Descriptor($aggregate, new EventSnapshot($entity->toArray()));
        }

        return $list;
    }

    /** @return array<int,Descritor> */
    public function listMaterialization(
        AggregateRoot $aggregate,
        DateTimeImmutable $initialMoment,
        Interval $interval
    ): array {
        $aggregateEvents = $this->query->eventListForRegisters(
            $this->query->aggregateListByDate($aggregate::label(), $initialMoment, $interval)
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
            $aggregateClass = $aggregate::class;

            /** @var AggregateRoot $entity */
            $entity = new $aggregateClass();

            $entity->estado()->setarDataCriacao($groupedOccurrenceList[$aggregateId][0]);

            foreach ($eventList as $index => $event) {
                $entity->consolidate([ $event ]);
                $entity->estado()->setarDataAlteracao($groupedOccurrenceList[$aggregateId][$index]);
            }

            $list[] = new Descriptor($aggregate, new EventSnapshot($entity->toArray()));
        }

        return $list;
    }

    public function remove(StreamId $streamId): void
    {
        $this->persistence->remove($streamId->aggregateId(), $streamId->version());
    }

    public function removePrevious(StreamId $streamId): void
    {
        $this->persistence->removePrevious($streamId->aggregateId(), $streamId->version());
    }

    public function removeAll(): void
    {
        $this->persistence->removeAll();
    }
}
