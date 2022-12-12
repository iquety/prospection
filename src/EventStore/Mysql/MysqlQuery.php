<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore\Mysql;

use DateTimeImmutable;
use Iquety\Prospection\EventStore\Error;
use Iquety\Prospection\EventStore\Interval;
use Iquety\Prospection\EventStore\Query;

class MysqlQuery implements Query
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

    /**
     * Devolve a lista de agregados, para ser usada em grades de dados.
     * A lista é baseada apenas no último instantaneo gerado e não possui
     * seus dados consolidados.
     * @return array<int,array<string,mixed>>
     */
    public function aggregateList(string $aggregateLabel, Interval $interval): array
    {
        $limit = $interval->registers();
        $offset = $interval->offset();

        $sql = "SELECT *, (
                    SELECT MAX(`version`) 
                    FROM {$this->eventsTable}
                    WHERE aggregate_id = `event`.aggregate_id
                ) AS last_version,
                (
                    SELECT COUNT(DISTINCT aggregate_id)
                    FROM {$this->eventsTable}
                    WHERE aggregate_label = ?
                ) AS aggregates_amount
            FROM {$this->eventsTable} AS `event`
            WHERE aggregate_label = ? AND
                  `version` = (
                    SELECT version FROM {$this->eventsTable}
                    WHERE aggregate_id = evento.aggregate_id AND `snapshot` = 1
                    ORDER BY version DESC
                    LIMIT 1
                  )
            LIMIT {$limit} OFFSET {$offset}
        ";

        return $this->connection->select($sql, [ $aggregateLabel, $aggregateLabel ]);
    }

    /**
     * Devolve a lista de agregados baseando-se em uma data inicial
     * @param DateTimeImmutable $initialMoment Momento da ocorrencia do evento
     * @return array<int,array<string,mixed>>
     */
    public function aggregateListByDate(
        string $aggregateLabel,
        DateTimeImmutable $initialMoment,
        Interval $interval
    ): array
    {
        $limit = $interval->registers();
        $offset = $interval->offset();

        $sql = "SELECT *, (
                    SELECT MAX(`version`) 
                    FROM {$this->eventsTable}
                    WHERE aggregate_id = `event`.aggregate_id
                ) AS last_version,
                (
                    SELECT COUNT(DISTINCT aggregate_id)
                    FROM {$this->eventsTable}
                    WHERE aggregate_label = ?
                ) AS aggregates_amount
            FROM {$this->eventsTable} AS `event`
            WHERE aggregate_label = ? AND
                  `version` = (
                    SELECT `version` FROM {$this->eventsTable}
                    WHERE aggregate_id = `event`.aggregate_id AND `snapshot` = 1
                    ORDER BY `version` DESC
                    LIMIT 1
                  ) AND
                  occurred_on >=?
            LIMIT {$limit} OFFSET {$offset}
        ";

        return $this->connection->select($sql, [
            $aggregateLabel,
            $aggregateLabel,
            $initialMoment->format('Y-m-d H:i:s.u')
        ]);
    }

    public function countEvents(): int
    {
        $sql = "SELECT COUNT(*) AS amount FROM {$this->eventsTable}";

        $results = current($this->connection->select($sql));
        return $results === false ? 0 : (int)$results['amount'];
    }

    public function countAggregateEvents(string $aggregateLabel, string $aggregateId): int
    {
        $sql = "SELECT COUNT(DISTINCT aggregate_id) AS amount FROM {$this->eventsTable} "
            . "WHERE aggregate_label = ?";

        $results = current($this->connection->select($sql, [ $aggregateLabel ]));
        return $results === false ? 0 : (int)$results['amount'];
    }

    /** Devolve a contagem de todos os eventos armazenados para o agregado */
    public function countAggregates(string $aggregateLabel): int
    {
        $sql = "SELECT COUNT(DISTINCT aggregate_id) AS amount FROM {$this->eventsTable} "
            . "WHERE aggregate_label = ?";

        $results = current($this->connection->select($sql, [ $aggregateLabel ]));
        return $results === false ? 0 : (int)$results['amount'];
    }

    /**
     * Devolve a lista de eventos a partir da versão especificada.
     * @return array<int,array<string,mixed>> */
    public function eventListForVersion(string $aggregateLabel, string $aggregateId, int $version): array
    {
        $sql = "SELECT * FROM {$this->eventsTable} 
            WHERE aggregate_label = ? AND aggregate_id = ? AND `version` >= ? 
            ORDER BY version ASC";

        return $this->connection->select($sql, [ $aggregateLabel, $aggregateId, $version ]);
    }

    /**
     * Devolve a lista de eventos para um agregado, partindo do
     * último instantâneo gerado
     * @return array<int,array<string,mixed>>
     */
    public function eventListForAggregate(string $aggregateLabel, string $aggregateId): array
    {
        $sql = "SELECT * FROM {$this->eventsTable} AS stream
            WHERE stream.aggregate_label = ? AND stream.aggregate_id = ? AND stream.version >= (
                SELECT `event`.version 
                FROM {$this->eventsTable} AS `event` 
                WHERE `event`.aggregate_id = stream.aggregate_id AND `event`.`snapshot`=1
                ORDER BY version DESC LIMIT 1
            )
        ";

        return $this->connection->select($sql, [ $aggregateLabel, $aggregateId ]);
    }

    /**
     * Devolve a lista de eventos, para ser usada na consolidação de uma
     * lista de agregados.
     *
     * Esta consulta utiliza uma cláusula longa, contendo os ids de agregados como filtro.
     * Na grande maioria dos casos, onde os agregados são usados para paginação, isso
     * será irrelevante. No entanto, consultas contendo muitos registros podem estourar
     * o limite aceito pela configuração do Mysql.
     * Cada consulta SQL é verificada pelo parâmetro max_allowed_packet, que limita o
     * tamanho em bytes, que uma cláusula pode ser recebida pelo Mysql.
     *
     * @see https://dev.mysql.com/doc/refman/8.0/en/server-system-variables.html#sysvar_max_allowed_packet
     * @param array<int,array<string,mixed>> $aggregateList
     * @return array<int,array<string,mixed>>
     */
    public function eventListForConsolidation(array $aggregateList): array
    {
        if ($aggregateList === []) {
            return [];
        }

        $filterList = [];
        foreach ($aggregateList as $register) {
            if ($register['version'] < $register['last_version']) {
                $filterList[] = "(" .
                    "aggregate_id = '{$register['aggregate_id']}' AND " .
                    "version >= {$register['version']}" .
                    ")";
            }
        }

        $sql = "SELECT *
                FROM {$this->eventsTable}
                WHERE " . implode(" OR ", $filterList);

        return $this->connection->select($sql);
    }

    public function hasError(): bool
    {
        return false;
    }

    public function lastError(): Error
    {
        return new Error('', '');
    }

    public function nextVersion(string $aggregateLabel, string $aggregateId): int
    {
        $sql = "SELECT IFNULL(MAX(version), 0) AS version 
            FROM {$this->eventsTable}
            WHERE aggregate_label = ? AND aggregate_id = ?";

        $results = current($this->connection->select($sql, [ $aggregateLabel, $aggregateId ]));
        return $results === false ? 1 : $results['version'] + 1;
    }
}
