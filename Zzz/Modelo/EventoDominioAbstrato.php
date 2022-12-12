<?php

declare(strict_types=1);

namespace Comum\Dominio\Modelo;

use Comum\Dominio\Modelo\Valores\DataHora;
use Comum\Dominio\Modelo\Valores\Hora;
use Comum\Evento\Instantaneo;
use Comum\Framework\Objeto;
use Comum\Framework\Vetor;
use DomainException;
use RuntimeException;

/**
 * Eventos de dominio devem possuir apenas objetos de valor (ObjetoValor,
 * ObjetoIdentidade, DataHora, Data, Hora) ou tipos primitivos simples ('string',
 * 'int', 'float', 'bool' e 'array'), para que sejam facilmente serializados
 * para gravação em banco de dados e deserialização para restabelecer o estado
 * de um agregado.
 */
abstract class EventoDominioAbstrato implements EventoDominio
{
    /** @var array<string,mixed> */
    private array $propriedades = [];

    /** @return array<string,mixed> */
    public function comoArray(): array
    {
        if ($this->propriedades !== []) {
            return $this->propriedades;
        }

        $this->propriedades = Objeto::capturar($this)->propriedadesAssociativas();

        if (isset($this->propriedades['idAgregado']) === false) {
            throw new RuntimeException("Um evento deve possuir o valor 'idAgregado'");
        }

        if (($this->propriedades['idAgregado'] instanceof ObjetoIdentidade) === false) {
            throw new RuntimeException(
                "O valor 'idAgregado' deve ser do tipo " . ObjetoIdentidade::class
            );
        }

        return $this->propriedades;
    }

    /**
     * Devolve os valores em seus formatos primitivos,
     * e com as datas e horas convertidas para UTC
     * @return array<string,mixed>
     */
    public function comoArraySerializavel(): array
    {
        $vetor = new Vetor($this->comoArrayUtc());

        /** @var array<string,mixed> */
        $lista = $vetor->comoArraySerializavel();
        return $lista;
    }

    /** @return array<string,mixed> */
    private function comoArrayUtc(): array
    {
        $listaValores = $this->comoArray();
        foreach ($listaValores as $indice => $valor) {
            if ($this->valorComFusoHorario($valor) === false) {
                continue;
            }

            $listaValores[$indice] = $valor->comFusoHorario('UTC');
        }

        return $listaValores;
    }

    /** @param mixed $valor */
    private function valorComFusoHorario($valor): bool
    {
        if (gettype($valor) !== 'object') {
            return false;
        }

        if (in_array($valor::class, [DataHora::class, Hora::class]) === false) {
            return false;
        }

        return true;
    }

    public function idAgregado(): ObjetoIdentidade
    {
        $propriedades = $this->comoArray();
        return $propriedades['idAgregado'];
    }

    abstract public function ocorridoEm(): DataHora;

    abstract public static function rotulo(): string;

    public function __toString(): string
    {
        $nome = Objeto::capturar($this)->nomeCurto();
        $valores = $this->comoArray();

        $vetor =  new Vetor($valores);
        $texto = $vetor->comoTexto();

        return $nome . " " . $texto;
    }
}
