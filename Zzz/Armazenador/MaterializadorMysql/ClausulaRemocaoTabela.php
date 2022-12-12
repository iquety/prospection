<?php

declare(strict_types=1);

namespace Comum\Infraestrutura\Evento\MaterializadorMysql;

class ClausulaRemocaoTabela
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Promoção de propriedades ainda não é suportada no PHPMD
     */
    public function __construct(private MaterializacaoMysql $materializacao)
    {
    }

    public function gerar(): string
    {
        return sprintf(
            "DROP TABLE IF EXISTS `%s`",
            $this->materializacao->tabela()
        );
    }
}
