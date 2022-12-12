<?php

declare(strict_types=1);

namespace Comum\Dominio\Modelo;

interface EntidadeRaiz extends Entidade
{
    /** @return array<string,mixed> */
    public function comoArray(): array;

    /**
     * Consolida o estado do agregado, aplicando nele a lista de eventos que
     * ocorreram até o presente momento, geralmente persistidos no banco de dados
     *
     * @param array<\Comum\Dominio\Modelo\EventoDominio> $variosEventos
     */
    public function consolidarEstado(array $variosEventos): void;

    public function estado(): EntidadeEstado;

    /**
     * Fábrica para geração do objeto de estado da entidade
     */
    public function fabricarEstado(): EntidadeEstado;

    /**
     * Aplica o evento na entidade, atualizando seu estado.
     * Os eventos aplicados também são armazenados em memória para obtenção
     * posterior usando método self::mudancas() com a finalidade de transmití-los
     * ou persistí-los.
     */
    public function mudarEstado(EventoDominio $eventoDominio): void;

    /**
     * Devolve a lista de eventos ocorridos após a fabricacao da entidade.
     * Esta lista pode ser usada para transmitir os novos eventos através de um
     * mecanismo de mensageria ou armazená-los no banco de dados.
     *
     * @return array<\Comum\Dominio\Modelo\EventoDominio>
     */
    public function mudancas(): array;
}
