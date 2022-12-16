<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore\Mysql;

use Closure;
use Iquety\Prospection\EventStore\Error;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

class MysqlConnection
{
    private ?PDO $pdo = null;

    private Error $error;

    /** @param array<string,mixed> $options*/
    public function __construct(
        private string $dbname,
        private string $host,
        private int $port,
        private string $user,
        private string $password,
        private array $options = []
    ) {
        $dns = 'mysql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->dbname;

        $this->error = new Error('', '');

        try {
            $this->pdo = new PDO($dns, $this->user, $this->password, $this->options);
        } catch (PDOException $exception) {
            $this->error = new Error((string)$exception->getCode(), $exception->getMessage());
        }
    }

    private function hasConnection(): bool
    {
        return $this->pdo !== null;
    }

    public function select(string $sql, array $bindedParams = []): array
    {
        if ($this->hasConnection() === false) {
            return [];
        }

        $statement = $this->pdo->prepare($sql);

        try {
            $statement->execute($bindedParams);
        } catch (PDOException $exception) {
            $this->error = new Error((string)$exception->getCode(), $exception->getMessage());

            return [];
        }

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $result !== false ? $result : [];
    }

    public function execute(string $sql, array $bindedParams = []): int
    {
        if ($this->hasConnection() === false) {
            return 0;
        }

        $statement = $this->pdo->prepare($sql);

        try {
            $statement->execute($bindedParams);
        } catch (PDOException $exception) {
            $this->error = new Error((string)$exception->getCode(), $exception->getMessage());

            return 0;
        }

        return $statement->rowCount();
    }

    public function transaction(Closure $operation): void
    {
        if ($this->hasConnection() === false) {
            return;
        }

        $this->pdo->beginTransaction();

        try {
            $operation();
        } catch (Throwable) {
            $this->pdo->rollBack();
        }

        $this->pdo->commit();
    }

    public function lastError(): Error
    {
        if ($this->hasConnection() === false) {
            return $this->error;
        }

        $info = $this->pdo->errorInfo();

        if ($info[0] !== '') {
            return $this->error;
        }

        // [0] => HY000                      -> SQLSTATE error code
        // [1] => 1                          -> Driver-specific error code.
        // [2] => near "bogus": syntax error -> Driver-specific error message.

        return new Error((string)$info[1], (string)$info[2]);
    }
}
