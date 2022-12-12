<?php

declare(strict_types=1);

namespace Comum\Dominio\Modelo;

/**
 * Um repositório é responsável por gerenciar a obtenção de dados de um Agregado.
 * Deve-se implementar um repositório para cada agregado inteiro.
 *
 * Evans aconselha que, um repositorio deve conter métods para buscar resultados.
 * O principal, é buscar um agregado por seu id. Mas podem existir outros, como
 * obterPeloNome(), obterPeloCpf(), etc...
 *
 * Vernon aconselha que seja implementado, também, um método para contar os
 * registros existentes.
 *
 * Outra dica é, ao inves de utilizar uma lista de instancias do agregado,
 * deve-se utilizar uma classe mais leve, contendo apenas os dados necessarios,
 * para representar o agregado, contendo também um método para fabricá-lo quando
 * for necessário.
 */
interface Repositorio
{
    // ...
}
