<?php

declare(strict_types=1);

namespace Comum\Infraestrutura\Evento\MaterializadorMysql;

use Comum\Dominio\Modelo\Valores\DataHora;
use Comum\Evento\Descritor;

class GeradorClausulas
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Promoção de propriedades ainda não é suportada no PHPMD
     */
    public function __construct(private MaterializacaoMysql $materializacao)
    {
    }

    /** @return array<string,string> */
    public function remocaoTabelas(): array
    {
        $clausulas = [];

        // tabela principal
        $tabelaNome = $this->materializacao->tabela();
        $clausulas[$tabelaNome] = (new ClausulaRemocaoTabela($this->materializacao))->gerar();

        /**
         * tabelas relacionadas
         * @var MaterializacaoMysql $materializacao
         */
        foreach ($this->materializacao->relacoes() as $materializacao) {
            $tabelaNome = $materializacao->tabela();
            $clausulas[$tabelaNome] = (new ClausulaRemocaoTabela($materializacao))->gerar();
        }

        return $clausulas;
    }

    /** @return array<string,string> */
    public function criacaoTabelas(): array
    {
        $clausulas = [];

        /**
         * tabelas relacionadas
         * @var MaterializacaoMysql $materializacao
         */
        foreach ($this->materializacao->relacoes() as $materializacao) {
            $tabelaNome = $materializacao->tabela();
            $clausulas[$tabelaNome] = (new ClausulaCriacaoTabela($materializacao))->gerar();
        }

        // tabela principal
        $tabelaNome = $this->materializacao->tabela();
        $clausulas[$tabelaNome] = (new ClausulaCriacaoTabela($this->materializacao))->gerar();

        return $clausulas;
    }

    public function insercaoPrincipal(Descritor $descritor): string
    {
        return (new ClausulaInsercao($this->materializacao, $descritor))->gerar();
    }

    /** @return array<string,string> */
    public function insercaoRelacoes(Descritor $descritor): array
    {
        $clausulas = [];

        /**
         * tabelas relacionadas
         * @var MaterializacaoMysql $materializacao
         */
        foreach ($this->materializacao->relacoes() as $materializacao) {
            $clausula = (new ClausulaInsercao($materializacao, $descritor));

            $sqlClausula = $clausula->gerar();
            $valorClausula = (string)current($clausula->obterValoresClausula());

            $identificador = ControleRelacoes::gerarIdentificador($materializacao->tabela(), $valorClausula);

            $clausulas[$identificador] = $sqlClausula;
        }

        return $clausulas;
    }

    public function exclusao(DataHora $dataHora): string
    {
        return (new ClausulaExclusao($this->materializacao, $dataHora))->gerar();
    }
}
