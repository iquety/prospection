<?php

declare(strict_types=1);

namespace Comum\Evento\Materializacao;

use Comum\Evento\Descritor;
use InvalidArgumentException;

class Valor
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Promoção de propriedades ainda não é suportada no PHPMD
     */
    public function __construct(
        private string $nomeValor,
        private string $nomeElemento = ''
    ) {
    }

    public function elemento(): string
    {
        return $this->nomeElemento;
    }

    public function extrairEstado(Descritor $descritor): int|float|string
    {
        $dados = $descritor->comoArraySerializavel();
        if (isset($dados[$this->nomeValor]) === false) {
            throw new InvalidArgumentException(
                "O descritor especificado não contém o valor '$this->nomeValor'"
            );
        }

        if (is_array($dados[$this->nomeValor]) === false) {
            return $dados[$this->nomeValor];
        }

        if (isset($dados[$this->nomeValor][$this->elemento()]) === false) {
            throw new InvalidArgumentException(
                "O descritor especificado não contém o elemento " .
                "'{$this->nomeElemento}' para o valor '$this->nomeValor'"
            );
        }

        return $dados[$this->nomeValor][$this->elemento()];
    }

    public function nome(): string
    {
        return $this->nomeValor;
    }
}
