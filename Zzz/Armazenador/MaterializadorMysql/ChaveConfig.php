<?php

declare(strict_types=1);

namespace Comum\Infraestrutura\Evento\MaterializadorMysql;

interface ChaveConfig
{
    public const TABELA_DESTINO = 'tabela_destino';
    public const TIPO = 'tipo';
    public const CAMPO_DESTINO = 'campo_destino';
    public const CAMPO_ORIGEM = 'campo_origem';

    public const TIPO_EXTRANGEIRA = 'extrangeira';
    public const TIPO_INDICE = 'indice';
    public const TIPO_PRIMARIA = 'primaria';
    public const TIPO_UNICA = 'unica';
}
