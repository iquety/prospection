<?php

declare(strict_types=1);

namespace Comum\Evento\Materializacao;

use Closure;

interface Materializacao
{
    public function campo(string $nomeCampo): Campo;

    public function contemCampo(string $nomeCampo): bool;

    public function contemRelacao(string $nomeCampo): bool;

    public function definirFabrica(Closure $rotina): void;

    public function existemRelacoes(): bool;

    /** @return array<string,Campo> */
    public function estrutura(): array;

    public function obterCampo(string $nomeCampo): Campo;

    public function obterRelacao(string $nomeCampo): Materializacao;

    public function relacao(string $nomeTabela, string $nomeCampo): Campo;

    /** @return array<string,Materializacao> */
    public function relacoes(): array;

    public function tabela(): string;
}
