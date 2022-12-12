<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore\Mysql;

use Closure;
use DateTimeImmutable;
use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\EventStore\Error;
use Iquety\Prospection\EventStore\Store;

class MysqlStore implements Store
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Promoção de propriedades ainda não é suportada no PHPMD
     */
    public function __construct(
        private MysqlConnection $connection,
        private string $eventsTable
    ) {
    }

    public function createTable(): void
    {
        $this->connection->execute("
            CREATE TABLE IF NOT EXISTS `{$this->eventsTable}` (
                aggregate_id VARCHAR(36) NOT NULL,
                aggregate_label VARCHAR(155) NOT NULL,
                event_label VARCHAR(155) NOT NULL,
                version INT(11) NOT NULL COMMENT 'Estado atual do agregado',
                snapshot INT(1) NOT NULL COMMENT 'Sinalizador de um estado completo',
                data TEXT NOT NULL COMMENT 'Dados serializados do evento',
                occurred_on TIMESTAMP NOT NULL COMMENT 'O momento que o evento aconteceu',
                PRIMARY KEY (`aggregate_id`, `version`)
            ) ENGINE=InnoDB;
        ");
    }

    public function removeTable(): void
    {
        $this->connection->execute("DROP TABLE IF EXISTS {$this->eventsTable}");
    }

    public function add(
        string $aggregateId,
        string $aggregateLabel,
        string $eventLabel,
        int $version,
        int $snapshot,
        string $eventData,
        DateTimeImmutable $occurredOn
    ): void {
        $content = [
            'aggregate_id'    => $aggregateId,
            'aggregate_label' => $aggregateLabel,
            'event_label'     => $eventLabel,
            'version'         => $version,
            'snapshot'        => $snapshot,
            'data'            => $eventData,
            'occurred_on'     => $occurredOn->format('Y-m-d H:i:s.u')
        ];

        $fields  = implode(",", array_keys($content));
        $values = array_values($content);

        $sql = "INSERT INTO {$this->eventsTable} ({$fields})
            VALUES ( ?, ?, ?, ?, ?, ?, ? )";

        $this->connection->execute($sql, $values);
    }

    public function hasError(): bool
    {
        return false;
    }

    public function lastError(): Error
    {
        return new Error('', '');
    }
    
    /**
     * Remove o evento especificado.
     * @todo Implementar restabelecimento da numeração de versões
     */
    public function remove(string $aggregateLabel, string $aggregateId, int $version): void
    {
        $sql = "DELETE FROM {$this->eventsTable} WHERE aggregate_id = ? AND `version` = ?";
        $this->connection->execute($sql, [ $aggregateId, $version ]);
    }

    /**
     * Remove o evento especificado.
     * @todo Implementar restabelecimento da numeração de versões
     */
    public function removePrevious(string $aggregateLabel, string $aggregateId, int $version): void
    {
        $sql = "DELETE FROM {$this->eventsTable} WHERE aggregate_id = ? AND `version` < ?";
        $this->connection->execute($sql, [ $aggregateId, $version ]);
    }

    public function removeAll(): void
    {
        $this->connection->execute("DELETE FROM {$this->eventsTable}");
    }

    public function transaction(Closure $operation): void
    {
        $this->connection->transaction($operation);

        // if ($this->connection->lastError()->message() !== '') {
        //     throw new RuntimeException($this->connection->lastError()->message());
        // }
    }
}
