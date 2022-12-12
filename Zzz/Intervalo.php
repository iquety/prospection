<?php

namespace Comum\Evento;

/**
 * O intervalo especifica a quantidade de registros o deslecamento de um ponteiro,
 * para a construção de uma paginação de agregados
 */
class Intervalo
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Promoção de propriedades ainda não é suportada no PHPMD
     */
    public function __construct(
        private int $registros,
        private int $deslocamento = 0
    ) {
    }

    /**
     * Devolve a quantidade de registros a serem devolvidos na lista
     */
    public function registros(): int
    {
        return $this->registros;
    }

    /**
     * Devolve a quantidade de registros que devem ser pulados no inicio da busca,
     * antes de começar contar os registros que devem ser devolvidos na lista
     */
    public function deslocamento(): int
    {
        return $this->deslocamento;
    }
}
