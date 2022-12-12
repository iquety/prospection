<?php

declare(strict_types=1);

namespace Comum\Dominio\Modelo;

use Comum\Dominio\Modelo\Valores\DataHora;

/**
 * Eventos de dominio devem possuir apenas objetos de valor (ObjetoValor,
 * ObjetoIdentidade, DataHora, Data, Hora) ou tipos primitivos simples ('string',
 * 'int', 'float', 'bool' e 'array'), para que sejam facilmente serializados
 * para gravação em banco de dados e deserialização para restabelecer o estado
 * de um agregado.
 */
interface EventoDominio
{
    /** @return array<string,mixed> */
    public function comoArray(): array;

    /**
     * Devolve os valores em seus formatos primitivos,
     * e com as datas e horas convertidas para UTC
     * @return array<string,mixed>
     */
    public function comoArraySerializavel(): array;

    public function idAgregado(): ObjetoIdentidade;

    public static function rotulo(): string;

    public function ocorridoEm(): DataHora;
}
