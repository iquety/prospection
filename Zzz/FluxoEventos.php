<?php

namespace Comum\Evento;

use Comum\Dominio\Modelo\EventoDominio;
use RuntimeException;

/**
 * Um fluxo de eventos contém todos os eventos executados em um determinado
 * periodo do tempo.
 */
class FluxoEventos
{
    /** @var array<EventoDominio> */
    private array $eventos = [];
    private int $versao = 0;

    public function adicionarEvento(EventoDominio $evento, int $versao): void
    {
        $this->eventos[] = $evento;

        if ($this->versao >= $versao) {
            throw new RuntimeException(
                "Este evento não pode ser adicionado porque está fora de sincronia"
            );
        }

        $this->versao = $versao;
    }

    /**
     * Todos os eventos ocorridos nno período
     * @return array<\Comum\Dominio\Modelo\EventoDominio>
     */
    public function eventos(): array
    {
        return $this->eventos;
    }

    /**
     * Obtém a versão do estado atual do agregado
     */
    public function versao(): int
    {
        return $this->versao;
    }
}
