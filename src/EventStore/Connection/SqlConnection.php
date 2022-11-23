<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore\Connection;

use Closure;
use DateTimeImmutable;
use Iquety\Prospection\Domain\IdentityObject;

interface SqlConnection
{
    public function select(string $sql, array $bindedParams = []): array;

    public function execute(string $sql, array $bindedParams = []): void;

    public function transaction(Closure $operation): void;

    public function lastError(): Error;
}
