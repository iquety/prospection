<?php

namespace Comum\Evento;

use Comum\Dominio\Modelo\EntidadeRaiz;
use Comum\Dominio\Modelo\EventoDominio;
use Comum\Dominio\Modelo\Valores\DataHora;
use Comum\Evento\FluxoEventos;

interface ArmazenadorEvento
{
    public function armazenar(EntidadeRaiz $agregado, EventoDominio $umEventoDominio): void;

    /** @param array<EventoDominio> $variosEventos */
    public function armazenarVarios(EntidadeRaiz $agregado, array $variosEventos): void;

    public function contarEventos(): int;

    public function contarRegistros(EntidadeRaiz $agregado): int;

    public function fluxoDesde(EntidadeRaiz $agregado, FluxoId $fluxoId): FluxoEventos;

    public function fluxoPara(EntidadeRaiz $agregado, string $idAgregado): FluxoEventos;

    /** @return array<int,Descritor> */
    public function listarRegistros(EntidadeRaiz $agregado, Intervalo $intervalo): array;

    /** @return array<int,Descritor> */
    public function listarRegistrosConsolidados(EntidadeRaiz $agregado, Intervalo $intervalo): array;

    /** @return array<int,Descritor> */
    public function listarRegistrosMaterializacao(
        EntidadeRaiz $agregado,
        DataHora $momentoInicial,
        Intervalo $intervalo
    ): array;

    public function remover(FluxoId $fluxoId): void;

    public function removerAnteriores(FluxoId $fluxoId): void;

    public function removerTodos(): void;
}
