<?php

declare(strict_types=1);

namespace Comum\Dominio\Modelo\Valores;

use DateTimeImmutable;

interface IDataHora
{
    public function tipoPrimitivo(): DateTimeImmutable;

    public function timestamp(): int;

    public function valor(): string;

    public function antesDe(IDataHora $outraData): bool;

    public function antesOuIgualA(IDataHora $outraData): bool;

    public function depoisDe(IDataHora $outraData): bool;

    public function depoisOuIgualA(IDataHora $outraData): bool;

    public function igualA(IDataHora $outraData): bool;

    public function __toString(): string;
}
