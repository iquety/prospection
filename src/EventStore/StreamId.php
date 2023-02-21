<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore;

use Iquety\Domain\Core\IdentityObject;

/**
 * Na literatura, a identificação mínima para um fluxo de evento é feita com
 * base em pelo menos dois dados:
 *    - a identificacao do agregado
 *    - a identificacao da versão do estado atual do agregado
 */
class StreamId
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Promoção de propriedades ainda não é suportada no PHPMD
     */
    public function __construct(
        private string $aggregateLabel,
        private IdentityObject $aggregateId,
        private int $version
    ) {
    }

    /**
     * Obtém a identificação da raiz de agregação para a qual
     * o fluxo de eventos ocorreu.
     */
    public function aggregateId(): IdentityObject
    {
        return $this->aggregateId;
    }

    /**
     * Obtém a identificação da raiz de agregação para a qual
     * o fluxo de eventos ocorreu.
     */
    public function aggregateLabel(): string
    {
        return $this->aggregateLabel;
    }

    public function equalTo(StreamId $other): bool
    {
        return $this->aggregateId()->equalTo($other->aggregateId())
            && $this->aggregateLabel() === $other->aggregateLabel()
            && $this->version() === $other->version();
    }

    /**
     * Obtém a 'versao do estado' de um fluxo de eventos. Quando um novo evento
     * é emitido para um determinado agregado, ele é armazenado em disco contendo
     * metadados referentes ao estado atual do referido agregado. Entre os metadados,
     * encontra-se um valor que determina a 'versão' do estado daquele agregado.
     *
     * Trata-se de um número incremental que funciona como uma sequência  na linha
     * do tempo do fluxo de alterações ocorridas.
     */
    public function version(): int
    {
        return $this->version;
    }

    public function withNewVersion(int $version): self
    {
        return new self($this->aggregateLabel(), $this->aggregateId(), $version);
    }
}
