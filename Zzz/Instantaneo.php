<?php

declare(strict_types=1);

namespace Comum\Evento;

use BadMethodCallException;
use Comum\Dominio\Modelo\EventoDominioAbstrato;
use Comum\Dominio\Modelo\ObjetoIdentidade;
use Comum\Dominio\Modelo\Valores\DataHora;
use InvalidArgumentException;

/**
 * Eventos de dominio devem possuir apenas objetos de valor (ObjetoValor,
 * ObjetoIdentidade, DataHora, Data, Hora) ou tipos primitivos simples ('string',
 * 'int', 'float', 'bool' e 'array'), para que sejam facilmente serializados
 * para gravação em banco de dados e deserialização para restabelecer o estado
 * de um agregado.
 */
class Instantaneo extends EventoDominioAbstrato
{
    /** @var array<string,mixed> $dados */
    private array $dados = [];

    /** @param array<string,mixed> $dados */
    public function __construct(array $dados)
    {
        if (isset($dados['idAgregado']) === false) {
            throw new InvalidArgumentException("Um evento deve possuir o valor 'idAgregado'");
        }

        if (($dados['idAgregado'] instanceof ObjetoIdentidade) === false) {
            throw new InvalidArgumentException(
                "O valor 'idAgregado' deve ser do tipo " . ObjetoIdentidade::class
            );
        }

        $this->dados = $dados;
    }

    /** @override */
    public function comoArray(): array
    {
        return $this->dados;
    }

    public function idAgregado(): ObjetoIdentidade
    {
        return $this->dados['idAgregado'];
    }

    public function ocorridoEm(): DataHora
    {
        return DataHora::agora();
    }

    public static function rotulo(): string
    {
        return 'instantaneo';
    }

    public static function rotuloAgregado(): string
    {
        throw new BadMethodCallException(
            "Instantâneos não possuem rótulos, pois seus agregados são dinâmicos"
        );
    }
}
