<?php

declare(strict_types=1);

namespace Iquety\Prospection\Domain\Stream;

use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;
use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\EventStore\EventSnapshot;
use OutOfRangeException;

/** @SuppressWarnings(PHPMD.TooManyPublicMethods) */
class State
{
    private bool $empty = true;

    /** @var array<string,mixed> */
    private array $state = [];

    /**
     * O estado da entidade é declarada na construção para controlar quais valores
     * deverão ser analisados no processo de consolidação
     * @param array<int,string> $stateValues */
    public function __construct(array $stateValues)
    {
        if ($stateValues === []) {
            throw new InvalidArgumentException(
                "The aggregation root must have a constructor containing the entity's state values"
            );
        }

        $hasAggregateId = false;

        foreach ($stateValues as $valueName) {
            if (! is_string($valueName)) {
                throw new InvalidArgumentException(
                    "The name of a value must be textual. " .
                    "The value '{$valueName}' provided is invalid"
                );
            }

            $this->state[$valueName] = 'undefined';

            if ($valueName === 'aggregateId') {
                $hasAggregateId = true;
            }
        }

        if ($hasAggregateId === false) {
            throw new InvalidArgumentException(
                "The aggregation root must have an entry for 'aggregateId'"
            );
        }

        $this->state['createdOn'] = 'undefined';
        $this->state['updatedOn'] = 'undefined';
    }

    public function checkAggregateId(IdentityObject $identity): void
    {
        if ($this->state['aggregateId']->value() !== $identity->value()) {
            throw new DomainException(
                "The aggregation ID contained in the event does not match the aggregation root ID"
            );
        }
    }

    public function checkState(): void
    {
        if (array_search('undefined', $this->state) !== false) {
            throw new DomainException(
                "Aggregate status is incomplete or not yet committed. " .
                "The first event of an aggregate's flow must always provide the complete state."
            );
        }
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        $this->checkState();

        return $this->state;
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    public function toSnapshot(): EventSnapshot
    {
        $this->checkState();

        return EventSnapshot::factory($this->toArray());
    }

    public function createdOn(): DateTimeImmutable
    {
        $this->checkState();

        return $this->state['createdOn'];
    }

    public function updatedOn(): DateTimeImmutable
    {
        $this->checkState();

        return $this->state['updatedOn'];
    }

    /** @return mixed */
    public function value(string $name)
    {
        if (isset($this->state[$name]) === false) {
            throw new OutOfRangeException(
                "The queried value '{$name}' does not belong to the current state"
            );
        }

        if ($this->state[$name] === 'undefined') {
            throw new DomainException(
                "The queried value '{$name}' is not filled yet"
            );
        }

        return $this->state[$name];
    }

    public function aggregateId(): IdentityObject
    {
        $this->checkState();

        return $this->state['aggregateId'];
    }

    public function change(DomainEvent $event): void
    {
        // Depois do primeiro evento, é preciso verificar a validade do id do agregado
        if ($this->empty === false) {
            $this->checkAggregateId($event->aggregateId());
        }

        if ($this->state['createdOn'] === 'undefined') {
            $this->state['createdOn'] = $event->occurredOn();
        }

        $stateValues = $event->toArray();

        foreach ($stateValues as $property => $value) {
            /** @var string $property */
            $this->state[$property] = $value;
        }

        $this->state['updatedOn'] = $event->occurredOn();

        // O primeiro evento deve conter o estado completo
        if ($this->empty === true) {
            $this->checkState();
        }

        $this->empty = false;
    }

    public function internalFactoryDateTime(DateTimeImmutable $createdOn): void
    {
        $this->state['createdOn'] = $createdOn;
        $this->state['updatedOn'] = $createdOn;
        $this->state['occurredOn'] = $createdOn;
    }
}
