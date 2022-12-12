<?php

declare(strict_types=1);

namespace Comum\Infraestrutura\Evento\MaterializadorMysql;

use Comum\Dominio\Modelo\EntidadeRaiz;
use Comum\Dominio\Modelo\Valores\DataHora;
use Comum\Evento\ArmazenadorEvento;
use Comum\Evento\Descritor;
use Comum\Evento\Intervalo;
use Comum\Evento\Materializacao\Materializacao;
use Comum\Evento\MaterializadorEvento;
use Comum\Infraestrutura\Framework\Persistencia\ConexaoMysql;
use RuntimeException;

/** @SuppressWarnings(PHPMD.TooManyPublicMethods) */
class MaterializadorEventoMysql implements MaterializadorEvento
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Promoção de propriedades ainda não é suportada no PHPMD
     */
    public function __construct(
        private ConexaoMysql $conexao,
        private ArmazenadorEvento $armazenador
    ) {
    }

    /**
     * Executa o processo de materialização dos agregados.
     * @param MaterializacaoMysql $materializacao
     */
    public function materializarAgregado(EntidadeRaiz $agregado, Materializacao $materializacao): void
    {
        ControleRelacoes::instancia()->reiniciar();

        $this->removerTabelasAgregado($materializacao);
        $this->criarTabelasAgregado($materializacao);

        $this->materializarRegistros($agregado, $materializacao, new DataHora('1980-01-10 10:10:10'));
    }

    /**
     * Executa o processo de materialização dos agregados.
     * @param MaterializacaoMysql $materializacao
     */
    public function materializarAgregadoDesde(
        EntidadeRaiz $agregado,
        Materializacao $materializacao,
        DataHora $dataInicio
    ): void {

        ControleRelacoes::instancia()->reiniciar();

        $this->removerTabelasAgregado($materializacao);
        $this->criarTabelasAgregado($materializacao);

        $this->materializarRegistros($agregado, $materializacao, $dataInicio);
    }

    /**
     * Executa o processo de materialização dos agregados.
     * @param MaterializacaoMysql $materializacao
     */
    public function materializarAtualizacoesDesde(
        EntidadeRaiz $agregado,
        Materializacao $materializacao,
        DataHora $dataInicio
    ): void {

        ControleRelacoes::instancia()->reiniciar();

        $this->removerRegistrosDesde($materializacao, $dataInicio);
        $this->carregarRelacoesPara($materializacao);

        $this->materializarRegistros($agregado, $materializacao, $dataInicio);
    }

    /**
     * Executa o processo de materialização dos agregados.
     * @param MaterializacaoMysql $materializacao
     */
    private function materializarRegistros(
        EntidadeRaiz $agregado,
        Materializacao $materializacao,
        DataHora $dataInicio
    ): void {

        $this->inserirRegistros($agregado, $materializacao, $dataInicio);
    }

    /** @param MaterializacaoMysql $materializacao */
    public function criarTabelasAgregado(Materializacao $materializacao): void
    {
        $gerador = new GeradorClausulas($materializacao);

        foreach ($gerador->criacaoTabelas() as $clausula) {
            $this->conexao->executar($clausula);
        }
    }

    /** @param MaterializacaoMysql $materializacao */
    public function removerTabelasAgregado(Materializacao $materializacao): void
    {
        $gerador = new GeradorClausulas($materializacao);
        foreach ($gerador->remocaoTabelas() as $clausula) {
            $this->conexao->executar($clausula);
        }
    }

    /** @param MaterializacaoMysql $materializacao */
    private function removerRegistrosDesde(
        MaterializacaoMysql $materializacao,
        DataHora $dataHora
    ): void {
        // remove os registros com a mesma data
        $gerador = new GeradorClausulas($materializacao);
        $clausulaExclusao = $gerador->exclusao($dataHora);

        try {
            $this->conexao->executar($clausulaExclusao);
        } catch (RuntimeException $e) {
            throw $e;
        }
    }

    /** @param MaterializacaoMysql $materializacao */
    private function inserirRegistros(
        EntidadeRaiz $agregado,
        Materializacao $materializacao,
        DataHora $dataInicio
    ): void {
        $listaRegistros = $this->armazenador->listarRegistrosMaterializacao(
            $agregado,
            $dataInicio,
            new Intervalo($this->armazenador->contarRegistros($agregado))
        );

        $gerador = new GeradorClausulas($materializacao);

        try {
            foreach ($listaRegistros as $descritor) {
                $idsRelacoes = $this->inserirRelacoes($gerador, $descritor);
                $clausulaPrincipal = $gerador->insercaoPrincipal($descritor);
                $this->conexao->executar($clausulaPrincipal, $idsRelacoes);
            }
        } catch (RuntimeException $e) {
            throw $e;
        }
    }

    /**
     * @uses ControleRelacoes
     * @return array<int,int>
     */
    private function inserirRelacoes(GeradorClausulas $gerador, Descritor $descritor): array
    {
        $idsRelacoes = [];

        $controle = ControleRelacoes::instancia();

        $listaRelacoes = $gerador->insercaoRelacoes($descritor);

        foreach ($listaRelacoes as $identificador => $clausula) {
            if ($controle->relacaoProcessada($identificador) === true) {
                $idsRelacoes[] = $controle->obterRelacaoProcessada($identificador);
                continue;
            }

            $this->conexao->executar($clausula);
            $identidade = (int)$this->conexao->ultimaIdentidade();

            $controle->marcarRelacaoProcessada($identificador, $identidade);
            $idsRelacoes[] = $identidade;
        }

        return $idsRelacoes;
    }

    /** @param MaterializacaoMysql $materializacao */
    private function carregarRelacoesPara(MaterializacaoMysql $materializacao): void
    {
        /**
         * tabelas relacionadas
         * @var MaterializacaoMysql $materializacao
         */
        foreach ($materializacao->relacoes() as $materializacao) {
            $this->carregarRelacoesDaTabela($materializacao->tabela());
        }
    }

    private function carregarRelacoesDaTabela(string $tabelaNome): void
    {

        $controle = ControleRelacoes::instancia();

        $clausula = sprintf('SELECT * FROM %s', $tabelaNome);
        $listaRelacoes = $this->conexao->selecionar($clausula);
        foreach ($listaRelacoes as $registro) {
            $identidade = array_shift($registro);
            $valor = array_shift($registro);

            $identificador = ControleRelacoes::gerarIdentificador($tabelaNome, $valor);

            $controle->marcarRelacaoProcessada($identificador, (int)$identidade);
        }
    }
}
