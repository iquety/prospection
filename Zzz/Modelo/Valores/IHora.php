<?php

declare(strict_types=1);

namespace Comum\Dominio\Modelo\Valores;

use DateTimeImmutable;

interface IHora
{
    public function tipoPrimitivo(): DateTimeImmutable;

    public function diferencaEmHoras(IHora $outroObjeto): int;
}
