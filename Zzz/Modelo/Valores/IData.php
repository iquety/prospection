<?php

declare(strict_types=1);

namespace Comum\Dominio\Modelo\Valores;

use DateTimeImmutable;

interface IData
{
    public function tipoPrimitivo(): DateTimeImmutable;

    public function diferencaEmDias(IData $outraData): int;
}
