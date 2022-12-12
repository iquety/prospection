<?php

declare(strict_types=1);

namespace Comum\Infraestrutura\Evento\MaterializadorMysql;

use Comum\Evento\Materializacao\Campo;
use Comum\Evento\Materializacao\Materializacao;
use Comum\Evento\Materializacao\MaterializacaoSimples;

/** @SuppressWarnings(PHPMD.TooManyPublicMethods) */
class MaterializacaoMysql extends MaterializacaoSimples implements Materializacao
{
    /** @override */
    public function __construct(string $nomeTabela)
    {
        parent::__construct($nomeTabela);

        $this->definirFabrica(function ($nomeTabela): Materializacao {
            return new MaterializacaoMysql($nomeTabela);
        });
    }

    /** @override */
    public function campo(string $nomeCampo): Campo
    {
        $campo = parent::campo($nomeCampo);

        if ($this->existemRelacoes() === false) {
        }
        if ($campo->nome() === 'id_relacao') {
            $campo->configuracao(CampoMysql::TIPO, CampoMysql::TIPO_CARACTERE);
        }

        return $campo;
    }

    public function binario(string $nomeCampo): Campo
    {
        $campo = $this->campo($nomeCampo);
        $campo->configuracao(CampoMysql::TIPO, CampoMysql::TIPO_BINARIO);
        return $campo;
    }

    public function caractere(string $nomeCampo, int $tamanho = 50): Campo
    {
        $campo = $this->campo($nomeCampo);
        $campo->configuracao(CampoMysql::TIPO, CampoMysql::TIPO_CARACTERE);
        $campo->configuracao(CampoMysql::TAMANHO, (string)$tamanho);
        return $campo;
    }

    public function data(string $nomeCampo): Campo
    {
        $campo = $this->campo($nomeCampo);
        $campo->configuracao(CampoMysql::TIPO, CampoMysql::TIPO_DATA);
        return $campo;
    }

    public function dataHora(string $nomeCampo, string $fusoHorario = "UTC"): Campo
    {
        $campo = $this->campo($nomeCampo);
        $campo->configuracao(CampoMysql::TIPO, CampoMysql::TIPO_DATAHORA);
        $campo->configuracao(CampoMysql::FUSO_HORARIO, $fusoHorario);
        return $campo;
    }

    public function decimal(string $nomeCampo, int $tamanho = 10, int $casasDecimais = 2): Campo
    {
        $campo = $this->campo($nomeCampo);
        $campo->configuracao(CampoMysql::TIPO, CampoMysql::TIPO_DECIMAL);
        $campo->configuracao(CampoMysql::TAMANHO, (string)$tamanho);
        $campo->configuracao(CampoMysql::CASAS_DECIMAIS, (string)$casasDecimais);
        return $campo;
    }

    public function hora(string $nomeCampo, string $fusoHorario = "UTC"): Campo
    {
        $campo = $this->campo($nomeCampo);
        $campo->configuracao(CampoMysql::TIPO, CampoMysql::TIPO_HORA);
        $campo->configuracao(CampoMysql::FUSO_HORARIO, $fusoHorario);
        return $campo;
    }

    public function identidade(string $nomeCampo, int $tamanho = 50): Campo
    {
        $campo = $this->campo($nomeCampo);
        $campo->configuracao(CampoMysql::TIPO, CampoMysql::TIPO_IDENTIDADE);
        $campo->configuracao(CampoMysql::TAMANHO, (string)$tamanho);

        $campoCriacao = $this->campo('criado_em');
        $campoCriacao->configuracao(CampoMysql::TIPO, CampoMysql::TIPO_CONTROLE_ESTADO);
        $campoCriacao->mapearValor('criadoEm');

        $campoAlteracao = $this->campo('alterado_em');
        $campoAlteracao->configuracao(CampoMysql::TIPO, CampoMysql::TIPO_CONTROLE_ESTADO);
        $campoAlteracao->mapearValor('alteradoEm');

        return $campo;
    }

    public function inteiro(string $nomeCampo, int $tamanho = 10): Campo
    {
        $campo = $this->campo($nomeCampo);
        $campo->configuracao(CampoMysql::TIPO, CampoMysql::TIPO_INTEIRO);
        $campo->configuracao(CampoMysql::TAMANHO, (string)$tamanho);
        return $campo;
    }

    /** @override */
    public function relacao(string $nomeTabela, string $nomeCampo): Campo
    {
        $campo = parent::relacao($nomeTabela, $nomeCampo);
        $campo->configuracao(CampoMysql::TIPO, CampoMysql::TIPO_CHAVE_EXTRANGEIRA);
        $campo->configuracao(CampoMysql::TAMANHO, '150');

        return $campo;
    }

    public function relacaoDimensionada(string $nomeTabela, string $nomeCampo, int $tamanho): Campo
    {
        $campo = parent::relacao($nomeTabela, $nomeCampo);
        $campo->configuracao(CampoMysql::TIPO, CampoMysql::TIPO_CHAVE_EXTRANGEIRA);
        $campo->configuracao(CampoMysql::TAMANHO, (string)$tamanho);

        return $campo;
    }

    public function texto(string $nomeCampo): Campo
    {
        $campo = $this->campo($nomeCampo);
        $campo->configuracao(CampoMysql::TIPO, CampoMysql::TIPO_TEXTO);
        return $campo;
    }

    /**
     * Quando um campo relacionado é criado, o mapeamento do valor é aplicado
     * apenas no campo da tabela principal. A tabela relacionada desconhece as alterações
     * efetuadas na tabela de origem. Este método sincroniza os dados corretamente.
     */
    protected function mapearRelacoes(): void
    {
        if ($this->mapeamentoPendente === false) {
            return;
        }

        foreach ($this->listaRelacoes as $nomeCampo => $materializacao) {
            $campoOriginal = $this->listaCampos[$nomeCampo];

            $campo = $materializacao->obterCampo($campoOriginal->nome());
            $campo->mapearValor(
                $campoOriginal->valor()->nome(),
                $campoOriginal->valor()->elemento()
            );

            $this->mapearConfiguracoes($campoOriginal, $campo);
        }

        $this->mapeamentoPendente = false;
    }

    private function mapearConfiguracoes(Campo $campoOriginal, Campo &$campoRelacao): void
    {
        $listaParametros = [ CampoMysql::TIPO, CampoMysql::TAMANHO ];

        foreach ($listaParametros as $parametro) {
            if ($campoOriginal->contemConfiguracao($parametro) === false) {
                continue;
            }

            $valor = $campoOriginal->obterConfiguracao($parametro);
            $campoRelacao->configuracao($parametro, $valor);
        }
    }
}
