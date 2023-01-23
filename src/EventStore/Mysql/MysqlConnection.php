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

    /**
     * @param array<string|int,mixed> $bindedParams
     * @return array<int,array<string,mixed>>
     */
    public function select(string $sql, array $bindedParams = []): array
    {
        if ($this->pdo === null) {
            return [];
        }

        $statement = $this->pdo->prepare($sql);

        try {
            $statement->execute($bindedParams);
        } catch (PDOException $exception) {
            $this->error = new Error((string)$exception->getCode(), $exception->getMessage());

            return [];
        }

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

     /** @param array<string|int,mixed> $bindedParams */
    public function execute(string $sql, array $bindedParams = []): int
    {
        if ($this->pdo === null) {
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

    public function transaction(Closure $operation, MysqlStore $store): void
    {
        if ($this->pdo === null) {
            return;
        }

        $this->pdo->beginTransaction();

        try {
            $operation($store);
        } catch (Throwable $exception) {
            $this->pdo->rollBack();

            $this->error = new Error((string)$exception->getCode(), $exception->getMessage());

            return;
        }

        $this->pdo->commit();
    }

    public function lastError(): Error
    {
        return $this->error;

        // if ($this->pdo === null) {
        //     return $this->error;
        // }

        // $info = $this->pdo->errorInfo();

        // if ($info[0] !== '') {
        //     return $this->error;
        // }

        // // [0] => HY000                      -> SQLSTATE error code
        // // [1] => 1                          -> Driver-specific error code.
        // // [2] => near "bogus": syntax error -> Driver-specific error message.

        // return new Error((string)$info[1], (string)$info[2]);
    }
}
