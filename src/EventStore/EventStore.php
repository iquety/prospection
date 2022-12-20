<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore;

use Closure;
use DateTimeImmutable;
use InvalidArgumentException;
use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\Domain\Stream\DomainEvent;
use Iquety\Prospection\Domain\Stream\StreamEntity;
use Iquety\PubSub\Event\Serializer\EventSerializer;
use RuntimeException;
use Throwable;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EventStore
{
    private const SNAPSHOT_SIZE = 10;

    /** @var array<string,class-string<DomainEvent>> */
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

    public function countAllEvents(): int
    {
        return $this->query->countEvents();
    }

    public function countAggregateEvents(string $aggregateSignature, IdentityObject $aggregateId): int
    {
        return $this->query->countAggregateEvents(
            $aggregateSignature::label(),
            $aggregateId->value()
        );
    }

    public function countAggregates(string $aggregateSignature): int
    {
        return $this->query->countAggregates($aggregateSignature::label());
    }

    /**
     * Devolve a lista de agregados, para ser usada em grades de dados.
     * A lista é baseada apenas no último instantaneo gerado e não possui
     * seus dados consolidados.
     * Cada agregado conterá dois valores adicionais:
     * - createdOn: ocorrência do primeiro evento do agregado
     * - updatedOn: ocorrência do último evento do agregado
     * @return array<int,Descriptor>
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function list(string $aggregateSignature, Interval $interval): array
    {
        $aggregateList = $this->query->aggregateList($aggregateSignature::label(), $interval);

        $list = [];
        foreach ($aggregateList as $register) {
            /** @var EventSnapshot $snapshot */
            $snapshot = EventSnapshot::factory(
                $this->serializer->unserialize($register['eventData'])
            );

            $list[] = new Descriptor(
                $aggregateSignature,
                $snapshot,
                new DateTimeImmutable($register['createdOn']),
                new DateTimeImmutable($register['updatedOn'])
            );
        }

        return $list;
    }

    /**
     * Devolve a lista de agregados, para ser usada em grades de dados.
     * Cada item da lista é o resultado da consolidação de todos os eventos ocorridos.
     * Cada agregado conterá dois valores adicionais:
     * - createdOn: ocorrência do primeiro evento do agregado
     * - updatedOn: ocorrência do último evento do agregado
     * @return array<int,Descriptor>
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function listConsolidated(string $aggregateSignature, Interval $interval): array
    {
        $aggregateEvents = $this->query->eventListForConsolidation(
            $this->query->aggregateList($aggregateSignature::label(), $interval)
        );

        $groupedByAggregate = [];
        foreach ($aggregateEvents as $eventRegister) {
            $aggregateId = $eventRegister['aggregateId'];

            if (isset($groupedByAggregate[$aggregateId]) === false) {
                $groupedByAggregate[$aggregateId] = [];
            }

            $groupedByAggregate[$aggregateId][] = $eventRegister;
        }

        $list = [];
        foreach ($groupedByAggregate as $aggregateId => $eventList) {
            $firstEvent = array_shift($eventList);

            /** @var StreamEntity $entity */
            $entity = $aggregateSignature::factory(
                $this->serializer->unserialize($firstEvent['eventData'])
            );

            $createdOn = '';
            $updatedOn = '';

            foreach ($eventList as $eventRegister) {
                $createdOn = $eventRegister['createdOn'];
                $updatedOn = $eventRegister['updatedOn'];

                $entity->consolidate([
                    $this->eventFactory(
                        $eventRegister['eventLabel'],
                        $this->serializer->unserialize($eventRegister['eventData'])
                    )
                 ]);
            }

            /** @var EventSnapshot */
            $snapshot = EventSnapshot::factory($entity->toArray());

            $list[] = new Descriptor(
                $aggregateSignature,
                $snapshot,
                new DateTimeImmutable($createdOn),
                new DateTimeImmutable($updatedOn)
            );
        }

        return $list;
    }

    /**
     * @return array<int,Descriptor>
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function listMaterialization(
        string $aggregateSignature,
        DateTimeImmutable $initialMoment,
        Interval $interval
    ): array {
        $aggregateEvents = $this->query->eventListForConsolidation(
            $this->query->aggregateListByDate($aggregateSignature::label(), $initialMoment, $interval)
        );

        $groupedByAggregate = [];
        foreach ($aggregateEvents as $eventRegister) {
            $aggregateId = $eventRegister['aggregateId'];

            if (isset($groupedByAggregate[$aggregateId]) === false) {
                $groupedByAggregate[$aggregateId] = [];
            }

            $groupedByAggregate[$aggregateId][] = $eventRegister;
        }

        $list = [];
        foreach ($groupedByAggregate as $aggregateId => $eventList) {
            $firstEvent = array_shift($eventList);

            /** @var StreamEntity $entity */
            $entity = $aggregateSignature::factory(
                $this->serializer->unserialize($firstEvent['eventData'])
            );

            $createdOn = '';
            $updatedOn = '';

            foreach ($eventList as $eventRegister) {
                $createdOn = $eventRegister['createdOn'];
                $updatedOn = $eventRegister['updatedOn'];

                $entity->consolidate([
                    $this->eventFactory(
                        $eventRegister['eventLabel'],
                        $this->serializer->unserialize($eventRegister['eventData'])
                    )
                 ]);
            }

            /** @var EventSnapshot */
            $snapshot = EventSnapshot::factory($entity->toArray());

            $list[] = new Descriptor(
                $aggregateSignature,
                $snapshot,
                new DateTimeImmutable($createdOn),
                new DateTimeImmutable($updatedOn)
            );
        }

        return $list;
    }

    /** @param class-string<DomainEvent> $eventSignature */
    public function registerEventType(string $eventSignature): void
    {
        $this->eventRegisterList[(string)$eventSignature::label()] = $eventSignature;
    }

    public function remove(
        string $aggregateSignature,
        IdentityObject $aggregateId,
        int $version
    ): void {
        $this->store->remove(
            $aggregateSignature::label(),
            $aggregateId->value(),
            $version
        );
    }

    public function removePrevious(
        string $aggregateSignature,
        IdentityObject $aggregateId,
        int $version
    ): void {
        $this->store->removePrevious(
            $aggregateSignature::label(),
            $aggregateId->value(),
            $version
        );
    }

    public function removeAll(): void
    {
        $this->store->removeAll();
    }

    public function store(string $aggregateSignature, DomainEvent $event): void
    {
        $this->storeMultiple($aggregateSignature, [ $event ]);
    }

    /**
     * @param array<DomainEvent> $domainEventList
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function storeMultiple(string $aggregateSignature, array $domainEventList): void
    {
        if ($domainEventList === []) {
            throw new InvalidArgumentException(
                "You must provide at least one event to store"
            );
        }

        $aggregateId = $domainEventList[0]->aggregateId();

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
                    if (! $event instanceof DomainEvent) {
                        throw new RuntimeException(
                            "Only domain events can be stored",
                            $commonExceptionCode
                        );
                    }

                    if ($aggregateId->value() !== $event->aggregateId()->value()) {
                        throw $diffAggregateException;
                    }

                    if (
                        ! $event instanceof EventSnapshot
                        && $aggregateLabel !== $event::aggregateLabel()
                    ) {
                        throw $diffAggregateException;
                    }

                    $version = $version === 0
                        ? $this->query->nextVersion($aggregateLabel, $aggregateId->value())
                        : $version + 1;

                    $snapshot = (int)($version === 1);

                    $this->store->add(
                        $aggregateId->value(),
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

    public function streamFor(string $aggregateSignature, IdentityObject $aggregateId): EventStream
    {
        $eventList = $this->query->eventListForAggregate(
            $aggregateSignature::label(),
            $aggregateId->value()
        );

        return $this->streamFactory($eventList);
    }

    public function streamSince(
        string $aggregateSignature,
        IdentityObject $aggregateId,
        int $version
    ): EventStream {
        if ($version === 0) {
            throw new InvalidArgumentException(
                'Invalid version provided. Event versions always start with 1'
            );
        }

        $eventList = $this->query->eventListForVersion(
            $aggregateSignature::label(),
            $aggregateId->value(),
            $version
        );

        return $this->streamFactory($eventList);
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

    /** @param array<string,mixed> $state */
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
    private function storeSnapshot(string $aggregateSignature, IdentityObject $aggregateId): void
    {
        $eventList = $this->streamFor($aggregateSignature, $aggregateId)->events();
        $stateValues = $eventList[0]->toArray();

        /** @var StreamEntity $aggregate */
        $aggregate = $aggregateSignature::factory($stateValues);
        $aggregate->consolidate($eventList);

        $event = $aggregate->toSnapshot();

        $this->store->add(
            $aggregateId->value(),
            $aggregateSignature::label(),
            $event::label(),
            $this->query->nextVersion($aggregateSignature::label(), $aggregateId->value()),
            1,
            $this->serializer->serialize($event->toArray()),
            new DateTimeImmutable()
        );
    }
}
