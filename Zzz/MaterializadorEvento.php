<?php

namespace Comum\Evento;

use Comum\Dominio\Modelo\EntidadeRaiz;
use Comum\Dominio\Modelo\Valores\DataHora;
use Comum\Evento\Materializacao\Materializacao;

interface MaterializadorEvento
{
    public function materializarAgregado(EntidadeRaiz $agregado, Materializacao $materializacao): void;

    public function materializarAgregadoDesde(
        EntidadeRaiz $agregado,
        Materializacao $materializacao,
        DataHora $dataInicio
    ): void;

    public function materializarAtualizacoesDesde(
        EntidadeRaiz $agregado,
        Materializacao $materializacao,
        DataHora $dataInicio
    ): void;
}
