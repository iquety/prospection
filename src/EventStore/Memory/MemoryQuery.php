<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore\Memory;

use DateTimeImmutable;
use Iquety\Prospection\EventStore\Error;
use Iquety\Prospection\EventStore\Interval;
use Iquety\Prospection\EventStore\Query;

class MemoryQuery implements Query
{
    private Error $error;
    
    public function __construct()
    {
        $this->error = new Error('', '');
    }
    
    private function connection(): MemoryConnection
    {
        return MemoryConnection::instance();
    }

    /**
     * Devolve a lista de agregados, para ser usada em grades de dados.
     * A lista é baseada apenas no último instantaneo gerado e não possui
     * seus dados consolidados.
     * Cada agregado conterá dois valores adicionais:
     * - createdOn: ocorrência do primeiro evento do agregado
     * - updatedOn: ocorrência do último evento do agregado
     * @return array<int,array<string,mixed>>
     */
    public function aggregateList(string $aggregateLabel, Interval $interval): array
    {
        $limit = $interval->registers();
        $offset = $interval->offset();

        $eventlist = $this->connection()->all();

        $aggregateList = [];
        $creations = [];
        $updates = [];
        $lastVersions = [];
        
        // somente agregados do tipo especificado
        foreach ($eventlist as $event) {
            if ($event['aggregateLabel'] !== $aggregateLabel) {
                continue;
            }

            $id = $event['aggregateId'];

            if (isset($creations[$id]) === false) {
                $creations[$id] = $event['occurredOn'];
            }

            $updates[$id] = $event['occurredOn'];
            $lastVersions[$id] = $event['version'];

            if ($event['snapshot'] === 1) {
                // o primeiro evento adicionado na lista define sua ordem
                // isso permite ordenar os agregados pela sua data de criação
                $aggregateList[$id] = $event;
            }

            $aggregateList[$id]['createdOn'] = $creations[$id];
            $aggregateList[$id]['updatedOn'] = $updates[$id];
            $aggregateList[$id]['lastVersion'] = $lastVersions[$id];
        }

        // total de entidades sem o filtro do intervalo
        $entityCount = count($aggregateList);
        foreach ($aggregateList as $index => $event) {
            $aggregateList[$index]['entityCount'] = $entityCount;
        }

        $listInterval = array_slice($aggregateList, $offset, $limit);

        return array_values($listInterval); // para indexar sequencialmente
    }

    /**
     * Devolve a lista de agregados baseando-se em uma data inicial.
     * A lista é baseada apenas no último instantaneo gerado e não possui
     * seus dados consolidados.
     * Cada agregado conterá dois valores adicionais:
     * - createdOn: ocorrência do primeiro evento do agregado
     * - updatedOn: ocorrência do último evento do agregado
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

        $aggregateList = [];
        
        $list = $this->aggregateList($aggregateLabel, new Interval(9999));

        foreach($list as $event) {
            if (new DateTimeImmutable($event['occurredOn']) >= $initialMoment) {
                $aggregateList[] = $event;
            }
        }

        // total de entidades sem o filtro do intervalo
        $entityCount = count($aggregateList);
        foreach ($aggregateList as $index => $event) {
            $aggregateList[$index]['entityCount'] = $entityCount;
        }

        $listInterval = array_slice($aggregateList, $offset, $limit);
        
        return array_values($listInterval); // para indexar sequencialmente
    }

    public function countEvents(): int
    {
        return count($this->connection()->all());
    }

    public function countAggregateEvents(
        string $aggregateLabel,
        string $aggregateId
    ): int {
        $eventList = [];

        $list = $this->connection()->all();

        foreach($list as $event) {
            if ($event['aggregateLabel'] !== $aggregateLabel) {
                continue;
            }

            $id = $event['aggregateId'];

            if ($id !== $aggregateId) {
                continue;
            }

            $eventList[] = $id;
        }

        return count($eventList);
    }

    public function countAggregates(string $aggregateLabel): int
    {
        $entities = [];

        $list = $this->connection()->all();

        foreach($list as $event) {
            if ($event['aggregateLabel'] !== $aggregateLabel) {
                continue;
            }

            $id = $event['aggregateId'];

            $entities[$id] = $id;
        }

        return count($entities);
    }

    /**
     * Devolve a lista de eventos a partir da versão especificada.
     * @return array<int,array<string,mixed>> */
    public function eventListForVersion(
        string $aggregateLabel,
        string $aggregateId,
        int $version
    ): array {
        $eventList = [];

        $list = $this->connection()->all();

        $count = 0;
        $createdOn = null;
        $updatedOn = null;

        foreach($list as $eventRegister) {
            $isAggregateEvent = $eventRegister['aggregateLabel'] === $aggregateLabel
                && $eventRegister['aggregateId'] === $aggregateId
                && $eventRegister['version'] >= $version;

            if ($isAggregateEvent === false) {
                continue;
            }

            if ($createdOn === null) {
                $createdOn = $eventRegister['occurredOn'];
            }

            $updatedOn = $eventRegister['occurredOn'];

            $eventList[] = $eventRegister;

            $count++;
        }

        for ($x = 0; $x < $count; $x++) {
            $eventList[$x]['createdOn'] = $createdOn;
            $eventList[$x]['updatedOn'] = $updatedOn;
        }

        return $eventList;
    }

    /**
     * Devolve a lista de eventos para consolidar um agregado, partindo do
     * último instantâneo gerado
     * @return array<int,array<string,mixed>>
     */
    public function eventListForAggregate(
        string $aggregateLabel,
        string $aggregateId
    ): array {
        $list = $this->eventListForVersion($aggregateLabel, $aggregateId, 1);

        if ($list === []) {
            return [];
        }

        $lastSnapshot = $list[0];

        foreach ($list as $event) {
            if ($event['snapshot'] === 1) {
                // o último evento a sobrescrever é o último instantâneo
                $lastSnapshot = $event;
            }
        }

        return $this->eventListForVersion($aggregateLabel, $aggregateId, $lastSnapshot['version']);
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

        $eventList = [];

        foreach ($aggregateList as $register) {
            $aggregateLabel = $register['aggregateLabel'];
            $aggregateId = $register['aggregateId'];

            $eventList = [
                ...$eventList,
                ...$this->eventListForAggregate($aggregateLabel, $aggregateId)
            ];
        }

        return $eventList;
    }

    public function hasError(): bool
    {
        return $this->error->message() !== '';
    }

    public function lastError(): Error
    {
        return $this->error;
    }

    public function nextVersion(string $aggregateLabel, string $aggregateId): int
    {
        $eventList = $this->eventListForVersion($aggregateLabel, $aggregateId, 1);

        if ($eventList == []) {
            return 1;
        }

        $lastIndex = array_key_last($eventList);

        $lastEvent = $eventList[$lastIndex];

        return $lastEvent['version'] + 1;
    }
}
