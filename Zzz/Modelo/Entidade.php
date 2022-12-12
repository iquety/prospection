<?php

declare(strict_types=1);

namespace Comum\Dominio\Modelo;

/**
 * As entidades são diferenciadas através de suas identidades.
 */
interface Entidade
{
    public function identidade(): ObjetoIdentidade;

    public function igualA(Entidade $outraEntidade): bool;

    public static function rotulo(): string;

    public function __toString(): string;
}
