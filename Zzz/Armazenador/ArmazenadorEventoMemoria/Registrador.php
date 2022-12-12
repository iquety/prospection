<?php

declare(strict_types=1);

namespace Comum\Infraestrutura\Evento\ArmazenadorEventoMemoria;

use Closure;
use Comum\Dominio\Modelo\ObjetoIdentidade;
use Comum\Dominio\Modelo\Valores\DataHora;
use Comum\Infraestrutura\Framework\Persistencia\ConexaoMemoria;

class Registrador
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Promoção de propriedades ainda não é suportada no PHPMD
     */
    public function __construct(
        private ConexaoMemoria $conexao,
        private string $tabelaEventos
    ) {
    }

    public function adicionar(
        string $idAgregado,
        string $rotuloAgregado,
        string $rotuloEvento,
        int $versao,
        int $instantaneo,
        string $dadosEvento,
        DataHora $ocorridoEm
    ): void {
        $this->conexao->executar("INSERT INTO {$this->tabelaEventos}", [
            'id_agregado'     => $idAgregado,
            'rotulo_agregado' => $rotuloAgregado,
            'rotulo_evento'   => $rotuloEvento,
            'versao'          => $versao,
            'instantaneo'     => $instantaneo,
            'dados'           => $dadosEvento,
            'ocorrido_em'     => $ocorridoEm->valorUtc()
        ]);
    }

    /**
     * Remove o evento especificado.
     * @todo Implementar restabelecimento da numeração de versões
     */
    public function remover(ObjetoIdentidade $idAgregado, int $versao): void
    {
        $this->conexao->executar("DELETE FROM {$this->tabelaEventos}", [
            'id_agregado' => $idAgregado->valor(),
            'versao' => $versao
        ]);
    }

    /**
     * Remove o evento especificado.
     * @todo Implementar restabelecimento da numeração de versões
     */
    public function removerAnteriores(ObjetoIdentidade $idAgregado, int $versao): void
    {
        $listaEventos = $this->conexao->selecionar("SELECT * FROM {$this->tabelaEventos}", [
            'id_agregado' => $idAgregado->valor()
        ]);

        foreach ($listaEventos as $registro) {
            if ($registro['versao'] < $versao) {
                $this->remover($idAgregado, $registro['versao']);
            }
        }
    }

    public function removerTodos(): void
    {
        $this->conexao->executar("DELETE FROM {$this->tabelaEventos}");
    }

    public function transacao(Closure $operacao): void
    {
        $operacao();
    }
}
