<?php

declare(strict_types=1);

namespace Comum\Infraestrutura\Evento\MaterializadorMysql;

use Comum\Evento\Descritor;
use Comum\Evento\Materializacao\Campo;

class ClausulaInsercao
{
    private bool $relacao = false;

    /** @var array<string> */
    private array $valoresClausula = [];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Promoção de propriedades ainda não é suportada no PHPMD
     */
    public function __construct(
        private MaterializacaoMysql $materializacao,
        private Descritor $descritor
    ) {
        if ($materializacao->contemCampo('id_relacao') === true) {
            $this->relacao = true;
        }
    }

    public function gerar(): string
    {
        if ($this->relacao === true) {
            return $this->gerarClausulaRelacao();
        }

        return $this->gerarClausulaPrincipal();
    }

    private function gerarClausulaPrincipal(): string
    {
        $nomeTabela = $this->materializacao->tabela();

        $sqlCampos     = [];
        $sqlLacunas    = [];
        $sqlValores    = [];

        $listaCampos = $this->materializacao->estrutura();
        foreach ($listaCampos as $campo) {
            $sqlInfo = $this->obterValorPrincipal($campo);

            $sqlCampos[] = $campo->nome();
            $sqlLacunas[] = $sqlInfo['lacuna'];

            if ($sqlInfo['valor'] !== '?') {
                $sqlValores[] = $sqlInfo['valor'];
            }
        }

        $this->valoresClausula = $sqlValores;

        $campos  = implode(', ', $sqlCampos);
        $lacunas = implode(', ', $sqlLacunas);
        return sprintf("INSERT INTO `$nomeTabela` ($campos) VALUES ($lacunas)", ...$sqlValores);
    }

    /** @return array<string,mixed> */
    private function obterValorPrincipal(Campo $campo): array
    {
        $tipo = $campo->obterConfiguracao(CampoMysql::TIPO);

        $valor = '?';

        if ($tipo !== CampoMysql::TIPO_CHAVE_EXTRANGEIRA) {
            $valor = $campo->valor()->extrairEstado($this->descritor);
        }

        return [
            'valor' => $valor,
            'lacuna' => $this->sqlLacuna($valor)
        ];
    }

    private function gerarClausulaRelacao(): string
    {
        $nomeTabela = $this->materializacao->tabela();

        $sqlCampo = '';
        $sqlLacuna = '';
        $sqlValor = '';

        $listaCampos = $this->materializacao->estrutura();
        foreach ($listaCampos as $campo) {
            if ($campo->nome() === 'id_relacao') {
                continue;
            }

            $sqlInfo = $this->obterValorRelacao($campo);

            $sqlCampo = $campo->nome();
            $sqlLacuna = $sqlInfo['lacuna'];
            $sqlValor = $sqlInfo['valor'];
        }

        $this->valoresClausula = [ $sqlValor ];

        return sprintf("INSERT INTO `{$nomeTabela}` ($sqlCampo) VALUES ($sqlLacuna)", $sqlValor);
    }

    /** @return array<string,mixed> */
    private function obterValorRelacao(Campo $campo): array
    {
        $valor = $campo->valor()->extrairEstado($this->descritor);

        return [
            'valor' => $valor,
            'lacuna' => $this->sqlLacuna($valor)
        ];
    }

    /** @param mixed $valor */
    private function sqlLacuna($valor): string
    {
        if ($valor === '?') {
            return '?';
        }

        $tipo = gettype($valor);

        if ($tipo === "integer") {
            return "%d";
        }

        if ($tipo === "double") {
            return "%f";
        }

        return "'%s'";
    }

    /** @return array<string> */
    public function obterValoresClausula(): array
    {
        return $this->valoresClausula;
    }
}
