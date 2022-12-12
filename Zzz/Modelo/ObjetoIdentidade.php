<?php

declare(strict_types=1);

namespace Comum\Dominio\Modelo;

/**
 * Os objetos de identidade tem o mesmo comportamento e regras dos objetos de valor.
 * A única diferença é que o retorno do método valor() sempre será uma string.
 */
interface ObjetoIdentidade
{
    public function igualA(ObjetoIdentidade $outraIdentidade): bool;

    public function valor(): string;

    public function __toString(): string;
}
