<?php

declare(strict_types=1);

namespace Iquety\Prospection\Domain\Stream;

use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\PubSub\Event\Event;

/**
 * Eventos de dominio devem possuir apenas objetos de valor (ObjetoValor,
 * ObjetoIdentidade, DateTimeImmutable) ou tipos primitivos simples ('string',
 * 'int', 'float', 'bool' e 'array'), para que sejam serializados e persistidos
 * em banco de dados.
 */
abstract class DomainEvent extends Event
{
    // todo: event está permitindo fabricar com occurredOn string
    abstract public function aggregateId(): IdentityObject;

    abstract public static function aggregateLabel(): string;

    abstract public static function label(): string;
}
