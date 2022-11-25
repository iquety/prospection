<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore;

use Closure;
use DateTimeImmutable;
use Iquety\Prospection\Domain\Core\IdentityObject;

interface Store
{
    public function add(
        string $aggregateId,
        string $aggregateLabel,
        string $eventLabel,
        int $version,
        int $snapshot,
        string $eventData,
        DateTimeImmutable $occurredOn
    ): void;

    /**
     * Remove o evento especificado.
     * @todo Implementar restabelecimento da numeração de versões
     */
    public function remove(IdentityObject $aggregateId, int $version): void;

    /**
     * Remove o evento especificado.
     * @todo Implementar restabelecimento da numeração de versões
     */
    public function removePrevious(IdentityObject $aggregateId, int $version): void;

    public function removeAll(): void;

    public function transaction(Closure $operation): void;
}
