<?php

declare(strict_types=1);

namespace Comum\Dominio\Modelo;

use Comum\Dominio\Modelo\EventoDominio;
use Comum\Framework\Objeto;
use Comum\Framework\Vetor;
use DomainException;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

abstract class EntidadeRaizAbstrata implements EntidadeRaiz
{
    /** @var array<int,\Comum\Dominio\Modelo\EventoDominio> */
    private array $mudancas = [];

    private ?EntidadeEstado $estado = null;

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    // Contrato EntidadeRaiz
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public function comoArray(): array
    {
        return $this->estado()->comoArray();
    }

    public function consolidarEstado(array $variosEventos): void
    {
        $primeiroEvento = array_shift($variosEventos);

        if ($primeiroEvento === null) {
            throw new InvalidArgumentException(
                "Para consolidar o estado de um agregado é preciso pelo menos um evento"
            );
        }

        $this->estado()->modificar($primeiroEvento);
        $this->certificarEstadoCompleto();

        foreach ($variosEventos as $evento) {
            $this->estado()->modificar($evento);
        }
    }

    public function estado(): EntidadeEstado
    {
        $this->inicializarEstado();
        /** @phpstan-ignore-next-line  */
        return $this->estado;
    }

    abstract public function fabricarEstado(): EntidadeEstado;

    abstract public static function rotulo(): string;

    public function mudarEstado(EventoDominio $evento): void
    {
        $this->estado()->modificar($evento);
        $this->certificarEstadoCompleto();

        $this->mudancas[] = $evento;
    }

    public function mudancas(): array
    {
        return $this->mudancas;
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    // Contrato Entidade
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public function identidade(): ObjetoIdentidade
    {
        return $this->estado()->idAgregado();
    }

    public function igualA(Entidade $outraEntidade): bool
    {
        return $this->identidade()->valor() === $outraEntidade->identidade()->valor();
    }

    public function __toString(): string
    {
        $nome = Objeto::capturar($this)->nomeCurto();
        $texto = "";

        try {
            $valores = $this->estado()->comoArray();
        } catch (DomainException $excecao) {
            $texto = "[" . PHP_EOL . "    estado incompleto" . PHP_EOL . "]";
        }

        if ($texto === "") {
            $vetor =  new Vetor($valores);
            $texto = $vetor->comoTexto();
        }

        return $nome . " " . $texto;
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    // Dependencias de implementação
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    private function certificarEstadoCompleto(): void
    {
        try {
            $this->estado()->verificarEstado();
        } catch (Throwable $erro) {
            throw new DomainException(
                $erro->getMessage(),
                $erro->getCode(),
                $erro
            );
        }
    }

    private function inicializarEstado(): void
    {
        if ($this->estado === null) {
            $this->estado = $this->fabricarEstado();
        }
    }
}
