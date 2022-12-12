<?php

declare(strict_types=1);

namespace Comum\Evento\Materializacao;

use Closure;
use OutOfRangeException;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class MaterializacaoSimples implements Materializacao
{
    private Closure $fabMaterializacao;

    /** @var array<string,Campo> */
    protected array $listaCampos = [];

    /** @var array<string,Materializacao> */
    protected array $listaRelacoes = [];

    protected bool $mapeamentoPendente = false;

    public function __construct(private string $nomeTabela)
    {
        $this->definirFabrica(function ($nomeTabela): Materializacao {
            return new MaterializacaoSimples($nomeTabela);
        });
    }

    public function campo(string $nomeCampo): Campo
    {
        $this->listaCampos[$nomeCampo] = new Campo($nomeCampo);
        return $this->listaCampos[$nomeCampo];
    }

    public function contemCampo(string $nomeCampo): bool
    {
        return isset($this->listaCampos[$nomeCampo]);
    }

    public function contemRelacao(string $nomeCampo): bool
    {
        return isset($this->listaRelacoes[$nomeCampo]);
    }

    public function definirFabrica(Closure $rotina): void
    {
        $this->fabMaterializacao = $rotina;
    }

    public function existemRelacoes(): bool
    {
        return $this->listaRelacoes !== [];
    }

    /** @return array<string,Campo> */
    public function estrutura(): array
    {
        $this->mapearRelacoes();

        return $this->listaCampos;
    }

    public function obterCampo(string $nomeCampo): Campo
    {
        $this->mapearRelacoes();

        return $this->listaCampos[$nomeCampo]
            ?? throw new OutOfRangeException("O campo '$nomeCampo' não existe");
    }

    public function obterRelacao(string $nomeCampo): Materializacao
    {
        $this->mapearRelacoes();

        return $this->listaRelacoes[$nomeCampo]
            ?? throw new OutOfRangeException("O campo '$nomeCampo' não é uma relação");
    }

    public function relacao(string $nomeTabela, string $nomeCampo): Campo
    {
        $this->fabricarRelacao($nomeTabela, $nomeCampo);

        $this->listaCampos[$nomeCampo] = $this->campo($nomeCampo)
            ->configuracao(Campo::CONFIG_TABELA_RELACIONADA, $nomeTabela)
            ->configuracao(Campo::CONFIG_CAMPO_RELACIONADO, 'id_relacao');

        $this->mapeamentoPendente = true;

        return $this->listaCampos[$nomeCampo];
    }

    private function fabricarRelacao(string $nomeTabela, string $nomeCampo): void
    {
        $fabrica = $this->fabMaterializacao;

        $materializacao = $fabrica($nomeTabela);
        $materializacao->campo('id_relacao');
        $materializacao->campo($nomeCampo);

        $this->listaRelacoes[$nomeCampo] = $materializacao;
    }

    /** @return array<string,Materializacao> */
    public function relacoes(): array
    {
        $this->mapearRelacoes();

        return $this->listaRelacoes;
    }

    public function tabela(): string
    {
        return $this->nomeTabela;
    }

    /**
     * Quando um campo relacionado é criado, o mapeamento do valor é aplicado
     * apenas no campo da tabela principal. A tabela relacionada desconhece as alterações
     * efetuadas na tabela de origem. Este método sincroniza os dados corretamente.
     */
    protected function mapearRelacoes(): void
    {
        if ($this->mapeamentoPendente === false) {
            return;
        }

        foreach ($this->listaRelacoes as $nomeCampo => $materializacao) {
            $campoOriginal = $this->listaCampos[$nomeCampo];

            $materializacao->obterCampo($campoOriginal->nome())
                ->mapearValor(
                    $campoOriginal->valor()->nome(),
                    $campoOriginal->valor()->elemento()
                );
        }

        $this->mapeamentoPendente = false;
    }
}
