<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore;

/**
 * O intervalo especifica a quantidade de registros o deslecamento de um ponteiro,
 * para a construção de uma paginação de agregados
 */
class Interval
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Promoção de propriedades ainda não é suportada no PHPMD
     */
    public function __construct(
        private int $registers,
        private int $offset = 0
    ) {
    }

    /**
     * Devolve a quantidade de registros a serem devolvidos na lista
     */
    public function registers(): int
    {
        return $this->registers;
    }

    /**
     * Devolve a quantidade de registros que devem ser pulados no inicio da busca,
     * antes de começar contar os registros que devem ser devolvidos na lista
     */
    public function offset(): int
    {
        return $this->offset;
    }
}
