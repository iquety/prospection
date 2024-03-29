<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore;

use DateTimeImmutable;
use Iquety\Domain\Core\IdentityObject;
use Iquety\Prospection\EventStore\Interval;

interface Query
{
    public const ERROR_CONNECTION = '1';

    public const ERROR_QUERY = '2';

    /**
     * Devolve a lista de agregados, para ser usada em grades de dados.
     * A lista é baseada apenas no último instantaneo gerado e não possui
     * seus dados consolidados.
     * Cada agregado conterá dois valores adicionais:
     * - createdOn: ocorrência do primeiro evento do agregado
     * - updatedOn: ocorrência do último evento do agregado
     * @return array<int,array<string,mixed>>
     */
    public function aggregateList(string $aggregateLabel, Interval $interval): array;

    /**
     * Devolve a lista de agregados baseando-se em uma data inicial
     * @param DateTimeImmutable $initialMoment Momento da ocorrencia do evento
     * @return array<int,array<string,mixed>>
     */
    public function aggregateListByDate(
        string $aggregateLabel,
        DateTimeImmutable $initialMoment,
        Interval $interval
    ): array;

    /** Devolve a contagem de todos os eventos armazenados até o momento */
    public function countEvents(): int;

    public function countAggregateEvents(string $aggregateLabel, string $aggregateId): int;

    /** Devolve a contagem de todos os eventos armazenados para o agregado */
    public function countAggregates(string $aggregateLabel): int;

    /**
     * Devolve a lista de eventos a partir da versão especificada.
     * @return array<int,array<string,mixed>> */
    public function eventListForVersion(
        string $aggregateLabel,
        string $aggregateId,
        int $version
    ): array;

    /**
     * Devolve a lista de eventos para um agregado, partindo do
     * último instantâneo gerado
     * @return array<int,array<string,mixed>>
     */
    public function eventListForAggregate(
        string $aggregateLabel,
        string $aggregateId
    ): array;

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
    public function eventListForConsolidation(array $aggregateList): array;

    public function hasError(): bool;

    public function lastError(): Error;

    public function nextVersion(string $aggregateLabel, string $aggregateId): int;
}
