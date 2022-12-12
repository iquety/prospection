<?php

declare(strict_types=1);

namespace Comum\Infraestrutura\Evento\ArmazenadorEventoMysql;

use Comum\Dominio\Modelo\Valores\DataHora;
use Comum\Evento\Intervalo;
use Comum\Infraestrutura\Framework\Persistencia\ConexaoMysql;

class Consultador
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Promoção de propriedades ainda não é suportada no PHPMD
     */
    public function __construct(
        private ConexaoMysql $conexao,
        private string $tabelaEventos
    ) {
    }

    public function totalEventos(): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->tabelaEventos}";
        $resultado = current($this->conexao->selecionar($sql));
        return $resultado === false ? 0 : (int)$resultado['total'];
    }

    public function totalRegistros(string $rotuloAgregado): int
    {
        $sql = "SELECT COUNT(DISTINCT id_agregado) as total FROM {$this->tabelaEventos} "
            . "WHERE rotulo_agregado = ?";
        $resultado = current($this->conexao->selecionar($sql, [ $rotuloAgregado ]));
        return $resultado === false ? 0 : (int)$resultado['total'];
    }

    public function proximaVersao(string $idAgregado): int
    {
        $sql = "SELECT IFNULL(MAX(versao), 0) as versao 
            FROM {$this->tabelaEventos}
            WHERE id_agregado = ?";
        $resultado = current($this->conexao->selecionar($sql, [ $idAgregado ]));
        return $resultado === false ? 1 : $resultado['versao'] + 1;
    }

    /**
     * Devolve a lista de eventos a partir da versão especificada.
     * @return array<int,array<string,mixed>> */
    public function listaEventosParaVersao(string $idAgregado, int $versao): array
    {
        $sql = "SELECT * FROM {$this->tabelaEventos} 
            WHERE id_agregado = ? AND versao >= ? 
            ORDER BY versao ASC";

        return $this->conexao->selecionar($sql, [ $idAgregado, $versao ]);
    }

    /**
     * Devolve a lista de eventos para um agregado, partindo do
     * último instantâneo gerado
     * @return array<int,array<string,mixed>>
     */
    public function listaEventosParaAgregado(string $idAgregado): array
    {
        $sql = "SELECT * FROM {$this->tabelaEventos} as fluxo
            WHERE fluxo.id_agregado = ? AND fluxo.versao >= (
                SELECT inst.versao 
                FROM {$this->tabelaEventos} as inst 
                WHERE inst.id_agregado = fluxo.id_agregado AND inst.instantaneo=1
                ORDER BY versao DESC LIMIT 1
            )
        ";

        return $this->conexao->selecionar($sql, [ $idAgregado ]);
    }

    /**
     * Devolve a lista de eventos, para ser usada na consolidação de uma
     * lista de agregados.
     *
     * Esta consulta utiliza uma cláusula longa, contendo os ids de agregados como filtro.
     * Na grande maioria dos casos, onde os agregados são usados para paginação, isso
     * será irrelevante. No entanto, consultas contendo muitos registros podem estourar
     * o limite aceito pela configuração do Mysql.
     * Cada consulta SQL é verificada pelo parâmetro max_allowed_packet, que limita o
     * tamanho em bytes, que uma cláusula pode ser recebida pelo Mysql.
     *
     * @see https://dev.mysql.com/doc/refman/8.0/en/server-system-variables.html#sysvar_max_allowed_packet
     * @param array<int,array<string,mixed>> $listaRegistros
     * @return array<int,array<string,mixed>>
     */
    public function listaEventosParaRegistros(array $listaRegistros): array
    {
        if ($listaRegistros === []) {
            return [];
        }

        $listaFiltros = [];
        foreach ($listaRegistros as $registro) {
            if ($registro['versao'] < $registro['ultima_versao']) {
                $listaFiltros[] = "(" .
                    "id_agregado = '{$registro['id_agregado']}' AND " .
                    "versao >= {$registro['versao']}" .
                    ")";
            }
        }

        $sql = "SELECT *
                FROM {$this->tabelaEventos}
                WHERE " . implode(" OR ", $listaFiltros);

        return $this->conexao->selecionar($sql);
    }

    /**
     * Devolve a lista de agregados, para ser usada em grades de dados.
     * A lista é baseada apenas no último instantaneo gerado e não possui
     * seus dados consolidados.
     * @return array<int,array<string,mixed>>
     */
    public function listaRegistros(string $rotuloAgregado, Intervalo $intervalo): array
    {
        $limite = $intervalo->registros();
        $deslocamento = $intervalo->deslocamento();

        $sql = "SELECT *, (
                    SELECT MAX(versao) 
                    FROM {$this->tabelaEventos}
                    WHERE id_agregado = evento.id_agregado
                ) AS ultima_versao,
                (
                    SELECT COUNT(DISTINCT id_agregado)
                    FROM {$this->tabelaEventos}
                    WHERE rotulo_agregado = ?
                ) AS total_agregados
            FROM {$this->tabelaEventos} as evento
            WHERE rotulo_agregado = ? AND
                  versao = (
                    SELECT versao FROM {$this->tabelaEventos}
                    WHERE id_agregado = evento.id_agregado AND instantaneo = 1
                    ORDER BY versao DESC
                    LIMIT 1
                  )
            LIMIT {$limite} OFFSET {$deslocamento}
        ";

        return $this->conexao->selecionar($sql, [ $rotuloAgregado, $rotuloAgregado ]);
    }

    /**
     * Devolve a lista de agregados baseando-se em uma data inicial
     * @param DataHora $momentoInicial Momento da ocorrencia do evento
     * @return array<int,array<string,mixed>>
     */
    public function listaRegistrosPorData(string $rotuloAgregado, DataHora $momentoInicial, Intervalo $intervalo): array
    {
        $limite = $intervalo->registros();
        $deslocamento = $intervalo->deslocamento();

        $sql = "SELECT *, (
                    SELECT MAX(versao) 
                    FROM {$this->tabelaEventos}
                    WHERE id_agregado = evento.id_agregado
                ) AS ultima_versao,
                (
                    SELECT COUNT(DISTINCT id_agregado)
                    FROM {$this->tabelaEventos}
                    WHERE rotulo_agregado = ?
                ) AS total_agregados
            FROM {$this->tabelaEventos} as evento
            WHERE rotulo_agregado = ? AND
                  versao = (
                    SELECT versao FROM {$this->tabelaEventos}
                    WHERE id_agregado = evento.id_agregado AND instantaneo = 1
                    ORDER BY versao DESC
                    LIMIT 1
                  ) AND
                  ocorrido_em >=?
            LIMIT {$limite} OFFSET {$deslocamento}
        ";

        return $this->conexao->selecionar($sql, [
            $rotuloAgregado,
            $rotuloAgregado,
            $momentoInicial->valorUtc()
        ]);
    }
}
