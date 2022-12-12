<?php

declare(strict_types=1);

namespace Comum\Dominio\Modelo;

/**
 * Os objetos de valor não possuem indentidade e são diferenciados pelos seus
 * valores. Um objeto de valor composto, contendo dois valores (ex: 'Nome' e 'Sobrenome'),
 * deve ser comparado levando em conta os dois valores que o compõe.
 */
interface ObjetoValor
{
    public function igualA(ObjetoValor $outroValor): bool;

    /** @return mixed */
    public function valor();

    public function __toString(): string;
}
