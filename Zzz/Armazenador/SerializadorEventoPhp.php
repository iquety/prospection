<?php

namespace Comum\Infraestrutura\Evento;

use Comum\Dominio\Modelo\EntidadeRaiz;
use Comum\Dominio\Modelo\EventoDominio;
use Comum\Evento\SerializadorEvento;
use RuntimeException;
use Throwable;

class SerializadorEventoPhp implements SerializadorEvento
{
    public function empacotar(EventoDominio $umEventoDominio): string
    {
        $dados = $umEventoDominio->comoArraySerializavel();
        return serialize($dados);
    }

    public function desempacotar(EntidadeRaiz $agregado, string $rotuloEvento, string $umaSerializacao): EventoDominio
    {
        try {
            $dados = unserialize($umaSerializacao);
        } catch (Throwable $excecao) {
            throw new RuntimeException("O evento serializado está corrompido: " . $excecao->getMessage());
        }

        return $agregado->estado()->fabricarEvento($rotuloEvento, $dados);
    }
}
