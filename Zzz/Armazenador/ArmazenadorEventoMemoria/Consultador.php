<?php

declare(strict_types=1);

namespace Comum\Infraestrutura\Evento\ArmazenadorEventoMemoria;

use Comum\Evento\Intervalo;
use Comum\Infraestrutura\Framework\Persistencia\ConexaoMemoria;

class Consultador
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

    public function totalEventos(): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->tabelaEventos}";
        $resultado = current($this->conexao->selecionar($sql));
        return $resultado === false ? 0 : (int)$resultado['COUNT'];
    }

    public function totalRegistros(string $rotuloAgregado): int
    {
        $sql = "SELECT COUNT(DISTINCT id_agregado) as total FROM {$this->tabelaEventos}";
        $resultado = current($this->conexao->selecionar($sql, [ 'rotulo_agregado' => $rotuloAgregado ]));
        return $resultado === false ? 0 : (int)$resultado['total'];
    }

    public function proximaVersao(string $idAgregado): int
    {
        $listaAgregados = $this->conexao
            ->selecionar("SELECT * FROM {$this->tabelaEventos}", [
                'id_agregado' => $idAgregado
            ]);

        $versaoAtual = 0;
        foreach ($listaAgregados as $registro) {
            if ($registro['id_agregado'] === $idAgregado) {
                $versaoAtual = $registro['versao'];
            }
        }

        return $versaoAtual + 1;
    }

    /**
     * Devolve a lista de eventos a partir da versão especificada.
     * @return array<int,array<string,mixed>> */
    public function listaEventosParaVersao(string $idAgregado, int $versao): array
    {
        $listaAgregados = $this->conexao->selecionar(
            "SELECT * FROM {$this->tabelaEventos}",
            [ 'id_agregado' => $idAgregado ]
        );

        $lista = [];
        foreach ($listaAgregados as $registro) {
            if ($registro['versao'] >= $versao) {
                $lista[] = $registro;
            }
        }

        return $lista;
    }

    /**
     * Devolve a lista de eventos para um agregado, partindo do
     * último instantâneo gerado
     * @return array<int,array<string,mixed>>
     */
    public function listaEventosParaAgregado(string $idAgregado): array
    {
        $listaEventos = $this->conexao->selecionar(
            "SELECT * FROM {$this->tabelaEventos}",
            [ 'id_agregado' => $idAgregado ]
        );

        $lista = [];
        foreach ($listaEventos as $registro) {
            if ($registro['instantaneo'] === 1) {
                $lista = [];
            }

            $lista[] = $registro;
        }

        return $lista;
    }

    /**
     * Devolve a lista de eventos, para ser usada na consolidação de uma
     * lista de agregados.
     *
     * @param array<int,array<string,mixed>> $listaRegistros
     * @return array<int,array<string,mixed>>
     */
    public function listaEventosParaRegistros(array $listaRegistros): array
    {
        if ($listaRegistros === []) {
            return [];
        }

        $lista = [];
        foreach ($listaRegistros as $registro) {
            if ($registro['versao'] < $registro['ultima_versao']) {
                $lista = array_merge($lista, $this->conexao->selecionar(
                    "SELECT * FROM {$this->tabelaEventos}",
                    [ 'id_agregado' => $registro['id_agregado'] ]
                ));
            }
        }

        return $lista;
    }

    /**
     * Devolve a lista de agregados, para ser usada em grades de dados.
     * A lista é baseada apenas no último instantaneo gerado e não possui
     * seus dados consolidados.
     * @return array<int,array<string,mixed>>
     */
    public function listaRegistros(string $rotuloAgregado, Intervalo $intervalo): array
    {
        $listaAgregados = $this->conexao->selecionar(
            "SELECT * FROM {$this->tabelaEventos}",
            [ 'rotulo_agregado' => $rotuloAgregado ]
        );

        // Cria uma lista de agregados com base no instantâneo
        $lista = [];
        foreach ($listaAgregados as $registro) {
            $idAgregado = $registro['id_agregado'];

            if ($registro['instantaneo'] === 1) {
                $lista[$idAgregado] = $registro;
            }

            if (isset($lista[$idAgregado]) === true) {
                $lista[$idAgregado]['ultima_versao'] = $registro['versao'];
            }
        }

        // Remove o deslocamento
        $contagem = 0;
        foreach ($lista as $indice => $registro) {
            if ($contagem < $intervalo->deslocamento()) {
                unset($lista[$indice]);
            }

            $contagem++;
        }

        // Obtém apenas o limite
        $contagem = 0;
        foreach ($lista as $indice => $registro) {
            if ($contagem >= $intervalo->registros()) {
                unset($lista[$indice]);
            }

            $contagem++;
        }

        return array_values($lista);
    }
}
