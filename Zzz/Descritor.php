<?php

namespace Comum\Evento;

use Comum\Dominio\Modelo\EntidadeRaiz;

/**
 * Um descritor contém os dados de um agregado de forma simplificada, para ser
 * alocado dentro de uma coleção consumindo o mínimo de memória possível e
 * garantindo a imutabilidade dos dados recebidos do armazenamento.
 */
class Descritor
{
    private bool $agregadoConsolidado = false;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Promoção de propriedades ainda não é suportada no PHPMD
     */
    public function __construct(
        private EntidadeRaiz $entidade,
        private Instantaneo $instantaneo
    ) {
    }

    /** @return array<string,mixed> */
    public function comoArray(): array
    {
        return $this->instantaneo->comoArray();
    }

    /** @return array<string,mixed> */
    public function comoArraySerializavel(): array
    {
        return $this->instantaneo->comoArraySerializavel();
    }

    public function comoAgregado(): EntidadeRaiz
    {
        if ($this->agregadoConsolidado === false) {
            $this->entidade->consolidarEstado([ $this->instantaneo ]);
            $this->agregadoConsolidado = true;
        }

        return $this->entidade;
    }

    public function __toString(): string
    {
        return (string)$this->instantaneo;
    }
}
