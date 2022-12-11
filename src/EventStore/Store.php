<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore;

use Closure;
use DateTimeImmutable;
use Iquety\Prospection\Domain\Core\IdentityObject;

interface Store
{
    public const ERROR_CONNECTION = '1';

    public const ERROR_ADD = '2';

    public const ERROR_CHANGE_VERSION = '3';

    public const ERROR_SORT_VERSIONS = '4';

    public const ERROR_REMOVE_ALL = '5';

    public const ERROR_TRANSACTION = '6';

    public function add(
        IdentityObject $aggregateId,
        string $aggregateLabel,
        string $eventLabel,
        int $version,
        int $snapshot,
        string $eventData,
        DateTimeImmutable $occurredOn
    ): void;

    public function hasError(): bool;

    public function lastError(): Error;

    /**
     * Remove o evento especificado.
     * Após a remoção, a sequencia das versões é restabelecida.
     */
    public function remove(
        string $aggregateLabel,
        IdentityObject $aggregateId,
        int $version
    ): void;

    /**
     * Remove o evento especificado.
     * Após a remoção, a sequencia das versões é restabelecida.
     */
    public function removePrevious(
        string $aggregateLabel,
        IdentityObject $aggregateId,
        int $version
    ): void;

    public function removeAll(): void;

    public function transaction(Closure $operation): void;
}
