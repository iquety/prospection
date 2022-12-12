<?php

namespace Comum\Evento;

use Comum\Dominio\Modelo\EntidadeRaiz;
use Comum\Dominio\Modelo\EventoDominio;

/**
 * A serialização de um evento é o meio de transmiti-lo através da rede,
 * Seja por meio de um gerenciador de mensagens (RabbitMQ, Kafka etc), ou
 * via API's REST
 */
interface SerializadorEvento
{
    public function empacotar(EventoDominio $umEventoDominio): string;

    public function desempacotar(EntidadeRaiz $agregado, string $rotuloEvento, string $umaSerializacao): EventoDominio;
}
