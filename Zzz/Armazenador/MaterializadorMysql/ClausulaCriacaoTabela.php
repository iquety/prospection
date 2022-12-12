<?php

declare(strict_types=1);

namespace Comum\Infraestrutura\Evento\MaterializadorMysql;

use Comum\Evento\Materializacao\Campo;
use OutOfBoundsException;

class ClausulaCriacaoTabela
{
    private bool $relacao = false;

    /** @var array<int,string> */
    private array $sqlCampos = [];

    /** @var array<int,string> */
    private array $sqlChaves = [];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Promoção de propriedades ainda não é suportada no PHPMD
     */
    public function __construct(private MaterializacaoMysql $materializacao)
    {
        if ($materializacao->contemCampo('id_relacao') === true) {
            $this->relacao = true;
        }
    }

    public function gerar(): string
    {
        $esboco = "CREATE TABLE IF NOT EXISTS `%s` (\n" . $this->clausulaCampos() . "\n) ENGINE=InnoDB";
        return sprintf($esboco, $this->materializacao->tabela());
    }

    private function clausulaCampos(): string
    {
        foreach ($this->materializacao->estrutura() as $campo) {
            if ($this->relacao === true) {
                $this->adicionarCampoRelacao($campo);
                continue;
            }

            $this->adicionarCampo($campo);
        }

        $notacao = array_merge($this->sqlCampos, $this->sqlChaves);

        return implode(",\n", $notacao);
    }

    private function adicionarCampoRelacao(Campo $campo): void
    {
        if ($campo->nome() === 'id_relacao') {
            $this->adicionarIdRelacao($campo);
            $this->adicionarChavePrimaria($campo);
            return;
        }

        $tamanho = (int)$campo->obterConfiguracao(CampoMysql::TAMANHO);
        $this->sqlCampos[] = sprintf("`%s` VARCHAR(%d) DEFAULT NULL", $campo->nome(), $tamanho);
    }

    // private function gerarCampoRelacionado(string $nomeCampo): string
    // {
    //     return sprintf("`%s` BIGINT UNSIGNED NOT NULL", $nomeCampo);
    // }

    private function adicionarCampo(Campo $campo): void
    {
        $tipo = $campo->obterConfiguracao(CampoMysql::TIPO);

        $camposDimensionaveis = [
            CampoMysql::TIPO_CARACTERE,
            CampoMysql::TIPO_DECIMAL,
            CampoMysql::TIPO_IDENTIDADE,
            CampoMysql::TIPO_INTEIRO,
        ];

        if (in_array($tipo, $camposDimensionaveis) === true) {
            $this->adicionarCampoDimensionavel($campo);
            return;
        }

        $notacao = $this->obterNotacao($tipo);

        if ($tipo === CampoMysql::TIPO_CHAVE_EXTRANGEIRA) {
            $this->adicionarChaveExtrangeira($campo);
            $this->sqlCampos[] = sprintf($notacao, $campo->nome());
            return;
        }

        if ($notacao === 'notacao_invalida') {
            throw new OutOfBoundsException(
                "O campo especificado não está configurado corretamente"
            );
        }

        $this->sqlCampos[] = sprintf($notacao, $campo->nome());
    }

    private function adicionarCampoDimensionavel(Campo $campo): void
    {
        $tipo    = $campo->obterConfiguracao(CampoMysql::TIPO);
        $tamanho = (int)$campo->obterConfiguracao(CampoMysql::TAMANHO);

        $notacao = $this->obterNotacao($tipo);

        if ($tipo === CampoMysql::TIPO_DECIMAL) {
            $casas = (int)$campo->obterConfiguracao(CampoMysql::CASAS_DECIMAIS);
            $this->sqlCampos[] = sprintf($notacao, $campo->nome(), $tamanho, $casas);
            return;
        }

        if ($tipo === CampoMysql::TIPO_IDENTIDADE) {
            $this->adicionarChavePrimaria($campo);
        }

        $this->sqlCampos[] = sprintf($notacao, $campo->nome(), $tamanho);
    }

    private function adicionarIdRelacao(Campo $campo): void
    {
        $this->sqlCampos[] = sprintf("`%s` BIGINT UNSIGNED AUTO_INCREMENT NOT NULL", $campo->nome());
    }

    private function adicionarChavePrimaria(Campo $campo): void
    {
        $this->sqlChaves[] = sprintf("PRIMARY KEY (%s)", $campo->nome());
    }

    private function adicionarChaveExtrangeira(Campo $campo): void
    {
        $this->sqlChaves[] = sprintf(
            "FOREIGN KEY (%s) REFERENCES %s(%s)",
            $campo->nome(),
            $campo->tabelaRelacionada(),
            $campo->campoRelacionado()
        );
    }

    private function obterNotacao(string $tipo): string
    {
        return match ($tipo) {
            // dimensionáveis
            CampoMysql::TIPO_CARACTERE => "`%s` VARCHAR(%d) DEFAULT NULL",
            CampoMysql::TIPO_DECIMAL => "`%s` DECIMAL(%d,%d) DEFAULT NULL",
            CampoMysql::TIPO_IDENTIDADE => "`%s` VARCHAR(%d) NOT NULL",
            CampoMysql::TIPO_INTEIRO => "`%s` INT(%d) DEFAULT NULL",
            // estáticos
            CampoMysql::TIPO_CONTROLE_ESTADO => "`%s` DATETIME DEFAULT NULL",
            CampoMysql::TIPO_BINARIO => "`%s` BLOB DEFAULT NULL",
            CampoMysql::TIPO_CHAVE_EXTRANGEIRA => "`%s` BIGINT UNSIGNED NOT NULL",
            CampoMysql::TIPO_DATA => "`%s` DATE DEFAULT NULL",
            CampoMysql::TIPO_DATAHORA => "`%s` DATETIME DEFAULT NULL",
            CampoMysql::TIPO_HORA => "`%s` TIME DEFAULT NULL",
            CampoMysql::TIPO_TEXTO => "`%s` TEXT DEFAULT NULL",
            default => 'notacao_invalida'
        };
    }
}
