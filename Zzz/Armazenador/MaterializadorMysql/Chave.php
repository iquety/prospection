<?php

declare(strict_types=1);

namespace Comum\Infraestrutura\Evento\MaterializadorMysql;

class Chave
{
    /** @var array<int,CampoChave> */
    private array $campos = [];

    /** @var array<string,mixed> */
    public array $configuracoes = [];

    public function configuracao(string $parametro, string $valor): self
    {
        $this->configuracoes[$parametro] = $valor;
        return $this;
    }

    /** @return array<CampoChave> */
    public function campos(): array
    {
        return $this->campos;
    }

    public function obterConfiguracao(string $parametro): string
    {
        return $this->configuracoes[$parametro] ?? '';
    }

    public function adicionarCampo(string $nomeCampo, int $tamanho = 0): self
    {
        $this->campos[] = new CampoChave($nomeCampo, $tamanho);
        return $this;
    }
}
