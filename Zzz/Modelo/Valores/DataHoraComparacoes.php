<?php

declare(strict_types=1);

namespace Comum\Dominio\Modelo\Valores;

trait DataHoraComparacoes
{
    protected function resolverFusoHorario(string $zona): string
    {
        return $zona === "Padrao" ? DataHora::fusoHorario() : $zona;
    }

    public function antesDe(IDataHora $outraData): bool
    {
        return $this->tipoPrimitivo() < $outraData->tipoPrimitivo();
    }

    public function antesOuIgualA(IDataHora $outraData): bool
    {
        return $this->tipoPrimitivo() <= $outraData->tipoPrimitivo();
    }

    public function depoisDe(IDataHora $outraData): bool
    {
        return $this->tipoPrimitivo() > $outraData->tipoPrimitivo();
    }

    public function depoisOuIgualA(IDataHora $outraData): bool
    {
        return $this->tipoPrimitivo() >= $outraData->tipoPrimitivo();
    }

    public function igualA(IDataHora $outraData): bool
    {
        return $this->tipoPrimitivo() == $outraData->tipoPrimitivo();
    }
}
