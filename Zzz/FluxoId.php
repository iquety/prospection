<?php

namespace Comum\Evento;

use Comum\Dominio\Modelo\ObjetoIdentidade;

/**
 * Na literatura, a identificação mínima para um fluxo de evento é feita com
 * base em pelo menos dois dados:
 *    - a identificacao do agregado
 *    - a identificacao da versão do estado atual do agregado
 */
class FluxoId
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Promoção de propriedades ainda não é suportada no PHPMD
     */
    public function __construct(
        private ObjetoIdentidade $idAgregado,
        private int $versao
    ) {
    }

    public function comNovaVersao(int $versao): self
    {
        return new self($this->idAgregado(), $versao);
    }

    /**
     * Obtém a identificação da raiz de agregação para a qual
     * o fluxo de eventos ocorreu.
     */
    public function idAgregado(): ObjetoIdentidade
    {
        return $this->idAgregado;
    }

    /**
     * Obtém a 'versao do estado' de um fluxo de eventos. Quando um novo evento
     * é emitido para um determinado agregado, ele é armazenado em disco contendo
     * metadados referentes ao estado atual do referido agregado. Entre os metadados,
     * encontra-se um valor que determina a 'versão' do estado daquele agregado.
     *
     * Trata-se de um número incremental que funciona como uma sequência  na linha
     * do tempo do fluxo de alterações ocorridas.
     */
    public function versao(): int
    {
        return $this->versao;
    }
}
