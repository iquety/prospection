<?php

declare(strict_types=1);

namespace Comum\Dominio\Modelo;

/**
 * Um serviço de domínio lida com vários objetos de domínio de forma atômica.
 * Diferente de uma Entidade, ou Repositório, que se limitam a conter ações
 * específicas de seu escopo, um serviço pode lidar com vários escopos ao mesmo
 * tempo.
 *
 * Por exemplo: uma Entidade Raiz contém apenas operações referentes aos
 * objetos que pertençam a esta agregação. Dessa forma, existe um Repositório
 * que lida com os dados somente desta agregação. Um serviço, pode fazer uso de
 * várias Entidades Raizes e vários Repositórios a fim de efetuar
 * operações mais complexas, que envolvam estes diferentes escopos em uma
 * única operação.
 *
 * Sempre que uma determinada operação, mesmo que não precise usar Entidades ou
 * Repositórios, precise "invadir" mais de um espaço de responsabilidade, ela
 * deve ser implementada em um serviço.
 */
interface Servico
{
   // ...
}
