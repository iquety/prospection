<?php

namespace Comum\Infraestrutura\Evento;

use Comum\Dominio\Modelo\EntidadeRaiz;
use Comum\Dominio\Modelo\EventoDominio;
use Comum\Dominio\Modelo\Valores\DataHora;
use Comum\Dominio\Modelo\Valores\Hora;
use Comum\Evento\Instantaneo;
use Comum\Evento\SerializadorEvento;
use Comum\Framework\Objeto;

class Empacotador
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Promoção de propriedades ainda não é suportada no PHPMD
     */
    public function __construct(
        private SerializadorEvento $serializador
    ) {
    }

    public function empacotar(EventoDominio $evento): string
    {
        return $this->serializador->empacotar($evento);
    }

    public function desempacotar(EntidadeRaiz $agregado, string $rotuloEvento, string $eventoSerializado): EventoDominio
    {
        $evento = $this->serializador->desempacotar(
            $agregado,
            $rotuloEvento,
            $eventoSerializado
        );

        return $this->restabelecerFusoHorario($evento);
    }

    public function restabelecerFusoHorario(EventoDominio $evento): EventoDominio
    {
        $eventoArgumentos = [];

        $listaArgumentos = $this->extrairArgumentos($evento);
        $listaValores = $evento->comoArray();

        foreach ($listaArgumentos as $argumento) {
            $nome = $argumento['nome'];
            $valor = $listaValores[$nome];

            if (in_array($argumento['tipo'], [DataHora::class, Hora::class]) === true) {
                $eventoArgumentos[$nome] = $valor->comFusoHorario(DataHora::fusoHorario());
                continue;
            }

            $eventoArgumentos[$nome] = $valor;
        }

        return $this->restabelecerEvento($evento::class, $eventoArgumentos);
    }

    /** @return array<int,array<string,string>> */
    private function extrairArgumentos(EventoDominio $evento): array
    {
        if ($evento::class !== Instantaneo::class) {
            return Objeto::capturar($evento)->argumentosConstrutor();
        }

        $listaValores = $evento->comoArray();
        $argumentos = [];
        foreach ($listaValores as $nome => $valor) {
            $argumentos[] = [
                'nome' => $nome,
                'tipo' => is_object($valor) ? $valor::class : 'desnecessario'
            ];
        }

        return $argumentos;
    }

    /** @param array<string,mixed> $argumentos */
    private function restabelecerEvento(string $eventoNome, array $argumentos): EventoDominio
    {
        if ($eventoNome === Instantaneo::class) {
            return new Instantaneo($argumentos);
        }

        return new $eventoNome(... $argumentos);
    }
}
