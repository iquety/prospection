<?php

declare(strict_types=1);

namespace Comum\Dominio\Modelo;

use Comum\Dominio\Modelo\EventoDominio;
use Comum\Dominio\Modelo\ObjetoIdentidade;
use Comum\Dominio\Modelo\Valores\DataHora;
use Comum\Evento\Instantaneo;
use Comum\Framework\Valor;
use Comum\Framework\Vetor;
use DomainException;
use ErrorException;
use InvalidArgumentException;
use OutOfRangeException;
use TypeError;

abstract class EntidadeEstadoAbstrata implements EntidadeEstado
{
    /** @var array<string,mixed> */
    private array $estado = [];

    /** @param array<int,string> $valoresEstado */
    public function __construct(array $valoresEstado)
    {
        if ($valoresEstado === []) {
            throw new InvalidArgumentException("A lista de valores devem conter pelo menos um valor");
        }

        $existeIdAgregado = false;

        foreach ($valoresEstado as $nomeValor) {
            if (! is_string($nomeValor)) {
                throw new InvalidArgumentException(
                    "O nome de um valor deve ser textual. O valor '{$nomeValor}' fornecido é inválido"
                );
            }

            $this->estado[$nomeValor] = 'indefinido';

            if ($nomeValor === 'idAgregado') {
                $existeIdAgregado = true;
            }
        }

        if ($existeIdAgregado === false) {
            throw new InvalidArgumentException("A lista de valores deve conter uma entrada para 'idAgregado'");
        }

        $this->estado['criadoEm'] = 'indefinido';
        $this->estado['alteradoEm'] = 'indefinido';
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    // Contrato EntidadeEstado
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    /** @return array<string,mixed> */
    public function comoArray(): array
    {
        $this->verificarEstado();
        return $this->estado;
    }

    public function comoEventoInstantaneo(): Instantaneo
    {
        $this->verificarEstado();

        $vetor = new Vetor($this->comoArray());

        /** @var array<string,mixed> */
        $valores = $vetor->comoArraySerializavel();

        /** @var Instantaneo */
        $evento = $this->fabricarEvento(Instantaneo::rotulo(), $valores);
        return $evento;
    }

    public function criadoEm(): DataHora
    {
        $this->verificarEstado();
        return $this->estado['criadoEm'];
    }

    public function alteradoEm(): DataHora
    {
        $this->verificarEstado();
        return $this->estado['alteradoEm'];
    }

    /** @return mixed */
    public function valor(string $nome)
    {
        if (isset($this->estado[$nome]) === false) {
            throw new OutOfRangeException("O valor '{$nome}' consultado não pertence ao estado atual");
        }

        if ($this->estado[$nome] === 'indefinido') {
            throw new DomainException("O valor '{$nome}' consultado ainda não foi preenchido");
        }

        return $this->estado[$nome];
    }

    /**
     * Este método é um auxiliar para a fábrica de eventos do agregado correspondente.
     * Cada tipo de evento ocorrido no agregado deve possuir uma implementação, utilizando
     * os dados primitivos fornecidos no segundo argumento para construir os objetos de
     * valor corretos. Ex:
     *
     * protected function criarNovoEvento(string $rotuloEvento, array $dados): ?EventoDominio
     * {
     *     if (Instantaneo::rotuloEvento() === $rotuloEvento) {
     *         return new Instantaneo('Aplicacao\\Produto', [
     *             new ValorId($dados['idAgregado']),
     *             $dados['titulo'],
     *             $dados['preco']
     *         ]);
     *     }
     *
     *     if (CadastroEfetuado::rotuloEvento() === $rotuloEvento) {
     *         return new CadastroEfetuado(
     *             new ValorId($dados['idAgregado']),
     *             $dados['titulo'],
     *             $dados['preco']
     *         );
     *     }
     *
     *     return null;
     * }
     *
     * @param array<string,mixed> $dados
     */
    abstract protected function criarNovoEvento(string $rotuloEvento, array $dados): ?EventoDominio;

    /** @param array<string,mixed> $dados */
    public function fabricarEvento(string $rotuloEvento, array $dados): EventoDominio
    {
        if ($this->apenasValoresPrimitivos($dados) === false) {
            throw new InvalidArgumentException(
                "Apenas valores primitivos podem ser usados para fabricação de eventos. " .
                "Os valores permitidos são: 'string', 'int', 'float' e 'array'"
            );
        }

        try {
            $evento = $this->criarNovoEvento($rotuloEvento, $dados);
        } catch (TypeError $excecao) {
            throw new ErrorException(
                "Não foi possível fabricar o evento rotulado como '{$rotuloEvento}'. " .
                "Motivo: " . $excecao->getMessage()
            );
        }

        if ($evento === null) {
            throw new DomainException(
                "Não foi possível fabricar o evento rotulado como '{$rotuloEvento}'. " .
                "Motivo: A fábrica não contém uma implementação para o evento especificado."
            );
        }

        return $evento;
    }

    public function idAgregado(): ObjetoIdentidade
    {
        $this->verificarEstado();
        return $this->estado['idAgregado'];
    }

    public function modificar(EventoDominio $evento): void
    {
        $estado = $evento->comoArray();

        if ($this->estado['criadoEm'] === 'indefinido') {
            $this->setarDataCriacao($evento->ocorridoEm());
        }

        foreach ($estado as $propriedade => $valor) {
            /** @var string $propriedade */
            $this->estado[$propriedade] = $valor;
        }

        $this->setarDataAlteracao($evento->ocorridoEm());
    }

    public function setarDataCriacao(DataHora $criadoEm): void
    {
        $this->estado['criadoEm'] = $criadoEm;
    }

    public function setarDataAlteracao(DataHora $alteradoEm): void
    {
        $this->estado['alteradoEm'] = $alteradoEm;
    }

    public function verificarEstado(): void
    {
        if (array_search('indefinido', $this->estado) !== false) {
            throw new DomainException(
                "O estado do agregado está incompleto ou ainda não foi consolidado. " .
                "O primeiro evento do fluxo de um agregado deve sempre fornecer o estado completo."
            );
        }
    }

    /** @param array<mixed> $dados */
    protected function apenasValoresPrimitivos(array $dados): bool
    {
        foreach ($dados as $valor) {
            if ((new Valor($valor))->tipoPrimitivo() === false) {
                return false;
            }
        }

        return true;
    }
}
