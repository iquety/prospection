<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore;

use BadMethodCallException;
use DateTimeImmutable;
use InvalidArgumentException;
use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\Domain\Stream\DomainEvent;

/**
 * Eventos de dominio devem possuir apenas objetos de valor (ObjetoValor,
 * ObjetoIdentidade, DataHora, Data, Hora) ou tipos primitivos simples ('string',
 * 'int', 'float', 'bool' e 'array'), para que sejam facilmente serializados
 * para gravação em banco de dados e deserialização para restabelecer o estado
 * de um agregado.
 */
class EventSnapshot extends DomainEvent
{
    /** @var array<string,mixed> $state */
    private array $state = [];

    /** @param array<string,mixed> $state */
    public function __construct(array $state)
    {
        if (isset($state['aggregateId']) === false) {
            throw new InvalidArgumentException("An event must have the value 'aggregateId'");
        }

        if (($state['aggregateId'] instanceof IdentityObject) === false) {
            throw new InvalidArgumentException(
                "The value 'aggregateId' must be of type " . IdentityObject::class
            );
        }

        $this->state = $state;
    }

    public function aggregateId(): IdentityObject
    {
        return $this->state['aggregateId'];
    }

    public static function aggregateLabel(): string
    {
        throw new BadMethodCallException(
            "Snapshots do not have labels as their aggregates are dynamic"
        );
    }

    public static function label(): string
    {
        return 'snapshot';
    }

    public function occurredOn(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    /** @override */
    public function toArray(): array
    {
        return $this->state;
    }
}
