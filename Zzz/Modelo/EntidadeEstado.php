<?php

declare(strict_types=1);

namespace Comum\Dominio\Modelo;

use Comum\Dominio\Modelo\EventoDominio;
use Comum\Dominio\Modelo\ObjetoIdentidade;
use Comum\Dominio\Modelo\Valores\DataHora;
use Comum\Evento\Instantaneo;

interface EntidadeEstado
{
    // public function alteradoEm(): DataHora;

    /** @return array<string,mixed> */
    public function comoArray(): array;

    public function comoEventoInstantaneo(): Instantaneo;

    public function criadoEm(): DataHora;

    public function alteradoEm(): DataHora;

    /** @param array<string,mixed> $dados */
    public function fabricarEvento(string $rotuloEvento, array $dados): EventoDominio;

    public function idAgregado(): ObjetoIdentidade;

    public function modificar(EventoDominio $evento): void;

    public function setarDataCriacao(DataHora $criadoEm): void;

    public function setarDataAlteracao(DataHora $alteradoEm): void;

    /** @return mixed */
    public function valor(string $nome);

    public function verificarEstado(): void;
}
