<?php

namespace Comum\Infraestrutura\Evento;

use Comum\Dominio\Modelo\EntidadeRaiz;
use Comum\Dominio\Modelo\EventoDominio;
use Comum\Dominio\Modelo\Valores\DataHora;
use Comum\Evento\ArmazenadorEvento;
use Comum\Evento\Descritor;
use Comum\Evento\FluxoEventos;
use Comum\Evento\FluxoId;
use Comum\Evento\Instantaneo;
use Comum\Evento\Intervalo;
use Comum\Evento\SerializadorEvento;
use Comum\Framework\Erro;
use Comum\Infraestrutura\Evento\ArmazenadorEventoMemoria\Consultador;
use Comum\Infraestrutura\Evento\ArmazenadorEventoMemoria\Registrador;
use Comum\Infraestrutura\Framework\Persistencia\ConexaoMemoria;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/** @SuppressWarnings(PHPMD.TooManyPublicMethods) */
class ArmazenadorEventoMemoria implements ArmazenadorEvento
{
    private const PERIODO_INSTANTANEO = 10;

    private Consultador $consultador;

    private Registrador $registrador;

    private Empacotador $empacotador;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Promoção de propriedades ainda não é suportada no PHPMD
     */
    public function __construct(
        ConexaoMemoria $conexao,
        SerializadorEvento $serializador,
        string $tabelaEventos = 'eventos'
    ) {
        $this->consultador   = new Consultador($conexao, $tabelaEventos);
        $this->registrador   = new Registrador($conexao, $tabelaEventos);
        $this->empacotador   = new Empacotador($serializador);
    }

    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
    // Contrato ArmazenadorEvento
    // - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    public function armazenar(EntidadeRaiz $agregado, EventoDominio $umEventoDominio): void
    {
        $this->armazenarVarios($agregado, [ $umEventoDominio ]);
    }

    /** @param array<\Comum\Dominio\Modelo\EventoDominio> $variosEventos */
    public function armazenarVarios(EntidadeRaiz $agregado, array $variosEventos): void
    {
        if ($variosEventos === []) {
            throw new InvalidArgumentException(
                "É preciso fornecer pelo menos um evento para armazenar"
            );
        }

        $idAgregado = $variosEventos[0]->idAgregado()->valor();

        $this->registrador->transacao(function () use ($agregado, $idAgregado, $variosEventos) {

            try {
                $versao = 0;

                foreach ($variosEventos as $eventoDominio) {
                    if ($idAgregado !== $eventoDominio->idAgregado()->valor()) {
                        throw new RuntimeException(
                            "Todos os eventos devem pertencer ao mesmo agregado",
                            100180
                        );
                    }

                    $versao = $versao === 0
                        ? $this->consultador->proximaVersao($idAgregado)
                        : $versao + 1;

                    $instantaneo = (int)($versao === 1);

                    $this->registrador->adicionar(
                        $idAgregado,
                        $agregado::rotulo(),
                        $eventoDominio::rotulo(),
                        $versao,
                        $instantaneo,
                        $this->empacotador->empacotar($eventoDominio),
                        $eventoDominio->ocorridoEm()
                    );

                    $gerarInstantaneo = ($versao % self::PERIODO_INSTANTANEO) === 0;
                    if ($gerarInstantaneo === true) {
                        $versao++;
                        $this->armazenarInstantaneo($agregado, $idAgregado);
                    }
                }
            } catch (Throwable $erro) {
                $mensagem = $erro->getCode() === 100180
                    ? $erro->getMessage()
                    : "Pode ser que o estado do agregado esteja incompleto. Erro: " . $erro->getMessage();

                throw new RuntimeException(
                    $mensagem . " na linha " . $erro->getLine() . " do arquivo " . $erro->getFile()
                );
            }
        });
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    private function armazenarInstantaneo(
        EntidadeRaiz $agregado,
        string $idAgregado
    ): void {

        /** @var $agregado EntidadeRaiz */
        $agregado->consolidarEstado($this->fluxoPara($agregado, $idAgregado)->eventos());

        $eventoDominio = $agregado->estado()->comoEventoInstantaneo();

        $this->registrador->adicionar(
            $idAgregado,
            $agregado::rotulo(),
            $eventoDominio::rotulo(),
            $this->consultador->proximaVersao($idAgregado),
            1,
            $this->empacotador->empacotar($eventoDominio),
            DataHora::agora()
        );
    }

    public function contarEventos(): int
    {
        return $this->consultador->totalEventos();
    }

    public function contarRegistros(EntidadeRaiz $agregado): int
    {
        return $this->consultador->totalRegistros($agregado::rotulo());
    }

    /** @return \Comum\Evento\FluxoEventos */
    public function fluxoDesde(EntidadeRaiz $agregado, FluxoId $fluxoId): FluxoEventos
    {
        $listaEventos = $this->consultador
            ->listaEventosParaVersao($fluxoId->idAgregado()->valor(), $fluxoId->versao());

        return $this->fabricarFluxo($agregado, $listaEventos);
    }

    /**
     * Devolve o fluxo de eventos para estabelecer o estado atual do agregado.
     * No fluxo, apenas os eventos a partir do último instantâneo.
     */
    public function fluxoPara(EntidadeRaiz $agregado, string $idAgregado): FluxoEventos
    {
        $listaEventos = $this->consultador->listaEventosParaAgregado($idAgregado);

        return $this->fabricarFluxo($agregado, $listaEventos);
    }

    /** @param array<array<string,mixed>> $listaEventos */
    private function fabricarFluxo(EntidadeRaiz $agregado, array $listaEventos): FluxoEventos
    {
        $fluxo = new FluxoEventos();
        foreach ($listaEventos as $registro) {
            $eventoDominio = $this->empacotador
                ->desempacotar($agregado, $registro['rotulo_evento'], $registro['dados']);
            $fluxo->adicionarEvento($eventoDominio, (int)$registro['versao']);
        }

        return $fluxo;
    }

    /** @return array<int,Descritor> */
    public function listarRegistros(EntidadeRaiz $agregado, Intervalo $intervalo): array
    {
        $listaRegistros = $this->consultador->listaRegistros($agregado::rotulo(), $intervalo);

        $lista = [];
        foreach ($listaRegistros as $registro) {
            /** @var Instantaneo $instantaneo */
            $instantaneo = $this->empacotador->desempacotar(
                $agregado,
                $registro['rotulo_evento'],
                $registro['dados']
            );

            $lista[] = new Descritor($agregado, $instantaneo);
        }

        return $lista;
    }

    /** @return array<int,Descritor> */
    public function listarRegistrosConsolidados(EntidadeRaiz $agregado, Intervalo $intervalo): array
    {
        $eventosPorAgregado = $this->consultador->listaEventosParaRegistros(
            $this->consultador->listaRegistros($agregado::rotulo(), $intervalo)
        );

        $agrupadosPorAgregado = [];
        foreach ($eventosPorAgregado as $registro) {
            $idAgregado = $registro['id_agregado'];

            if (isset($agrupadosPorAgregado[$idAgregado]) === false) {
                $agrupadosPorAgregado[$idAgregado] = [];
            }

            $agrupadosPorAgregado[$idAgregado][] = $this->empacotador
                ->desempacotar($agregado, $registro['rotulo_evento'], $registro['dados']);
        }

        $lista = [];

        foreach ($agrupadosPorAgregado as $idAgregado => $listaEventos) {
            $classeAgregado = $agregado::class;

            /** @var EntidadeRaiz $entidade */
            $entidade = new $classeAgregado();
            foreach ($listaEventos as $evento) {
                $entidade->consolidarEstado([ $evento ]);
            }

            $lista[] = new Descritor($agregado, new Instantaneo($entidade->comoArray()));
        }

        return $lista;
    }

    public function listarRegistrosMaterializacao(
        EntidadeRaiz $agregado,
        DataHora $momentoInicial,
        Intervalo $intervalo
    ): array {
        return [];
    }

    /**
     * Remove o evento especificado.
     * @todo Implementar restabelecimento da numeração de versões
     */
    public function remover(FluxoId $fluxoId): void
    {
        $this->registrador->remover(
            $fluxoId->idAgregado(),
            $fluxoId->versao()
        );
    }

    /**
     * Remove os eventos que ocorreram antes e reconsolida a sequência.
     * @todo Implementar restabelecimento da numeração de versões
     */
    public function removerAnteriores(FluxoId $fluxoId): void
    {
        $this->registrador->removerAnteriores(
            $fluxoId->idAgregado(),
            $fluxoId->versao()
        );
    }

    public function removerTodos(): void
    {
        $this->registrador->removerTodos();
    }
}
