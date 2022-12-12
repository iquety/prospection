<?php

declare(strict_types=1);

namespace Comum\Infraestrutura\Evento\MaterializadorMysql;

interface CampoMysql
{
    public const TAMANHO        = 'tamanho';
    public const TIPO           = 'tipo';
    public const CASAS_DECIMAIS = 'casas';
    public const FUSO_HORARIO   = 'fuso';

    public const TIPO_CONTROLE_ESTADO   = 'controle_estado';
    public const TIPO_BINARIO           = 'binario';
    public const TIPO_CAMPO_EXTRANGEIRO = 'campo_extrangeiro';
    public const TIPO_CHAVE_EXTRANGEIRA = 'chave_extrangeira';
    public const TIPO_CARACTERE         = 'caractere';
    public const TIPO_DATA              = 'data';
    public const TIPO_DATAHORA          = 'datahora';
    public const TIPO_DECIMAL           = 'decimal';
    public const TIPO_HORA              = 'hora';
    public const TIPO_IDENTIDADE        = 'identidade';
    public const TIPO_INTEIRO           = 'inteiro';
    public const TIPO_TEXTO             = 'texto';
}
