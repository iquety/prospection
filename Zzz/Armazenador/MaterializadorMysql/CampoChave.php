<?php

declare(strict_types=1);

namespace Comum\Infraestrutura\Evento\MaterializadorMysql;

class CampoChave
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Promoção de propriedades ainda não é suportada no PHPMD
     */
    public function __construct(private string $nome, private int $tamanho = 0)
    {
    }

    public function nome(): string
    {
        return $this->nome;
    }

    public function tamanho(): int
    {
        return $this->tamanho;
    }
}
