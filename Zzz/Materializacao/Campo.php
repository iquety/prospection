<?php

declare(strict_types=1);

namespace Comum\Evento\Materializacao;

use OutOfRangeException;

class Campo
{
    public const TIPO_ESPECIFICO = 'especifico';
    public const TIPO_INDETERMINADO = 'indeterminado';

    public const CONFIG_TABELA_RELACIONADA = 'tabela_relacionada';
    public const CONFIG_CAMPO_RELACIONADO = 'campo_relacionado';

    private string $tipo;

    private Valor $valor;

    /** @var array<string,mixed> */
    private array $configuracoes = [];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Promoção de propriedades ainda não é suportada no PHPMD
     */
    public function __construct(private string $nomeMaterializado)
    {
        $this->tipo = self::TIPO_INDETERMINADO;
        $this->valor = new Valor('indeterminado');
    }

    public function mapearValor(string $nomeValor, string $nomeElemento = ''): self
    {
        $this->tipo = self::TIPO_ESPECIFICO;
        $this->valor = new Valor($nomeValor, $nomeElemento);
        return $this;
    }

    public function configuracao(string $parametro, string $valor): self
    {
        $this->configuracoes[$parametro] = $valor;
        return $this;
    }

    public function tipo(): string
    {
        return $this->tipo;
    }

    public function contemConfiguracao(string $parametro): bool
    {
        return isset($this->configuracoes[$parametro]);
    }

    public function obterConfiguracao(string $parametro): string
    {
        return $this->configuracoes[$parametro]
            ?? throw new OutOfRangeException("O parâmetro de configuração '$parametro' não existe");
    }

    public function nome(): string
    {
        return $this->nomeMaterializado;
    }

    public function valor(): Valor
    {
        return $this->valor;
    }

    public function tabelaRelacionada(): string
    {
        return $this->obterConfiguracao(self::CONFIG_TABELA_RELACIONADA);
    }

    public function campoRelacionado(): string
    {
        return $this->obterConfiguracao(self::CONFIG_CAMPO_RELACIONADO);
    }
}
