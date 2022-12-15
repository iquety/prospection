<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore\Mysql;

use Closure;
use Iquety\Prospection\EventStore\Error;
use PDO;
use Throwable;

class MysqlConnection
{
    private PDO $pdo;

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
        
        $this->pdo = new PDO($dns, $this->user, $this->password, $this->options);
    }
    
    public function select(string $sql, array $bindedParams = []): array
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($bindedParams);

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $result !== false ? $result : [];
    }

    public function execute(string $sql, array $bindedParams = []): int
    {
        $statement = $this->pdo->prepare($sql);

        $statement->execute($bindedParams);

        return $statement->rowCount();
    }

    public function transaction(Closure $operation): void
    {
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
        $info = $this->pdo->errorInfo();
        
        // [0] => HY000                      -> SQLSTATE error code
        // [1] => 1                          -> Driver-specific error code.
        // [2] => near "bogus": syntax error -> Driver-specific error message.

        return new Error($info[1], $info[2]);
    }
}
