<?php

namespace Comum\Infraestrutura\Evento;

use Comum\Dominio\Modelo\EntidadeRaiz;
use Comum\Dominio\Modelo\EventoDominio;
use Comum\Evento\SerializadorEvento;
use RuntimeException;

class SerializadorEventoJson implements SerializadorEvento
{
    public function empacotar(EventoDominio $umEventoDominio): string
    {
        $dados = $umEventoDominio->comoArraySerializavel();
        $resultado = json_encode($dados, JSON_FORCE_OBJECT);

        return $resultado === false ? '' : $resultado;
    }

    public function desempacotar(EntidadeRaiz $agregado, string $rotuloEvento, string $umaSerializacao): EventoDominio
    {
        $dados = json_decode($umaSerializacao, true);
        $this->aferirErroDecodificacao();

        return $agregado->estado()->fabricarEvento($rotuloEvento, $dados);
    }

    private function aferirErroDecodificacao(): void
    {
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                return;
            case JSON_ERROR_SYNTAX:
                $erro = 'Syntax error, malformed JSON';
                break;
            // @codeCoverageIgnoreStart
            case JSON_ERROR_DEPTH:
                $erro = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $erro = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $erro = 'Unexpected control character found';
                break;
            case JSON_ERROR_UTF8:
                $erro = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $erro = 'Unknown error';
            // @codeCoverageIgnoreEnd
        }

        throw new RuntimeException("O evento serializado está corrompido: $erro");
    }
}
