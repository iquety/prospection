<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore\Connection;

use Closure;

class MysqlConnection implements SqlConnection
{
    public function select(string $sql, array $bindedParams = []): array
    {
        return [];
    }

    public function execute(string $sql, array $bindedParams = []): void
    {

    }

    public function transaction(Closure $operation): void
    {

    }

    public function lastError(): Error
    {
        return new Error('', '');
    }
}
