<?php

declare(strict_types=1);

namespace Comum\Infraestrutura\Evento\MaterializadorMysql;

use Comum\Dominio\Modelo\Valores\DataHora;

class ClausulaExclusao
{
    private bool $relacao = false;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Promoção de propriedades ainda não é suportada no PHPMD
     */
    public function __construct(
        private MaterializacaoMysql $materializacao,
        private DataHora $dataHora
    ) {
        if ($materializacao->contemCampo('id_relacao') === true) {
            $this->relacao = true;
        }
    }

    public function gerar(): string
    {
        if ($this->relacao === true) {
            return '';
        }

        return sprintf(
            "DELETE FROM `%s` WHERE `criado_em` >= '%s'",
            $this->materializacao->tabela(),
            $this->dataHora->valor()
        );
    }
}
