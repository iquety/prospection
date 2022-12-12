<?php

declare(strict_types=1);

namespace Comum\Infraestrutura\Evento\ArmazenadorEventoMysql;

use Closure;
use Comum\Dominio\Modelo\ObjetoIdentidade;
use Comum\Dominio\Modelo\Valores\DataHora;
use Comum\Framework\Erro;
use Comum\Infraestrutura\Framework\Persistencia\ConexaoMysql;
use RuntimeException;

class Registrador
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

    public function criarTabela(): void
    {
        $this->conexao->executar("
            CREATE TABLE IF NOT EXISTS `{$this->tabelaEventos}` (
                id_agregado varchar(36) NOT NULL,
                rotulo_agregado varchar(155) NOT NULL,
                rotulo_evento varchar(155) NOT NULL,
                versao int(11) NOT NULL COMMENT 'Estado atual do agregado',
                instantaneo int(1) NOT NULL COMMENT 'Sinalizador de um estado completo',
                dados TEXT NOT NULL COMMENT 'Dados serializados do evento',
                ocorrido_em TIMESTAMP NOT NULL COMMENT 'O momento que o evento aconteceu',
                PRIMARY KEY (`id_agregado`, `versao`)
            ) ENGINE=InnoDB;
        ");
    }

    public function removerTabela(): void
    {
        $this->conexao->executar("DROP TABLE IF EXISTS {$this->tabelaEventos}");
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
        $dados = [
            'id_agregado'     => $idAgregado,
            'rotulo_agregado' => $rotuloAgregado,
            'rotulo_evento'   => $rotuloEvento,
            'versao'          => $versao,
            'instantaneo'     => $instantaneo,
            'dados'           => $dadosEvento,
            'ocorrido_em'     => $ocorridoEm->valorUtc()
        ];

        $campos  = implode(",", array_keys($dados));
        $valores = array_values($dados);

        $sql = "INSERT INTO {$this->tabelaEventos} ({$campos})
            VALUES ( ?, ?, ?, ?, ?, ?, ? )";

        $this->conexao->executar($sql, $valores);
    }

    /**
     * Remove o evento especificado.
     * @todo Implementar restabelecimento da numeração de versões
     */
    public function remover(ObjetoIdentidade $idAgregado, int $versao): void
    {
        $sql = "DELETE FROM {$this->tabelaEventos} WHERE id_agregado = ? AND versao = ?";
        $this->conexao->executar($sql, [ $idAgregado->valor(), $versao ]);
    }

    /**
     * Remove o evento especificado.
     * @todo Implementar restabelecimento da numeração de versões
     */
    public function removerAnteriores(ObjetoIdentidade $idAgregado, int $versao): void
    {
        $sql = "DELETE FROM {$this->tabelaEventos} WHERE id_agregado = ? AND versao < ?";
        $this->conexao->executar($sql, [ $idAgregado->valor(), $versao ]);
    }

    public function removerTodos(): void
    {
        $this->conexao->executar("DELETE FROM {$this->tabelaEventos}");
    }

    public function transacao(Closure $operacao): void
    {
        $this->conexao->transacao($operacao);

        if ($this->conexao->ultimoErro()->mensagem() !== '') {
            throw new RuntimeException($this->conexao->ultimoErro()->mensagem());
        }
    }

    public function ultimoErro(): Erro
    {
        return $this->conexao->ultimoErro();
    }
}
