<?php

declare(strict_types=1);

namespace Comum\Infraestrutura\Evento\MaterializadorMysql;

class ControleRelacoes
{
    private static ?ControleRelacoes $instancia = null;

    /** @var array<string,int> */
    private array $relacoesProcessadas = [];

    public static function instancia(): self
    {
        if (static::$instancia === null) {
            static::$instancia = new self();
        }

        return static::$instancia;
    }

    protected function __construct()
    {
    }

    public function reiniciar(): void
    {
        $this->relacoesProcessadas  = [];
    }

    public static function gerarIdentificador(string $tabelaNome, string $valorArmazenado): string
    {
        return $tabelaNome . $valorArmazenado;
    }

    public function relacaoProcessada(string $identificador): bool
    {
        return isset($this->relacoesProcessadas[$identificador]);
    }

    public function marcarRelacaoProcessada(string $identificador, int $identidade): void
    {
        $this->relacoesProcessadas[$identificador] = $identidade;
    }

    public function obterRelacaoProcessada(string $identificador): int
    {
        return $this->relacoesProcessadas[$identificador];
    }
}
