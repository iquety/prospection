<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore;

use DateTimeImmutable;
use Iquety\Prospection\Stream\StreamEntity;

/**
 * Um descritor contém os dados de um agregado de forma simplificada, para ser
 * alocado dentro de uma coleção consumindo o mínimo de memória possível e
 * garantindo a imutabilidade dos dados recebidos do armazenamento.
 */
class Descriptor
{
    private ?StreamEntity $entity = null;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Promoção de propriedades ainda não é suportada no PHPMD
     */
    public function __construct(
        private string $aggregateSignature,
        private EventSnapshot $snapshot,
        private DateTimeImmutable $createdOn,
        private DateTimeImmutable $updatedOn
    ) {
    }

    public function createdOn(): DateTimeImmutable
    {
        return $this->createdOn;
    }

    public function updatedOn(): DateTimeImmutable
    {
        return $this->updatedOn;
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return $this->snapshot->toArray();
    }

    public function toAggregate(): StreamEntity
    {
        if ($this->entity === null) {
            $className = $this->aggregateSignature;

            $stateValues = $this->toArray();

            $this->entity = $className::factory($stateValues);
        }

        return $this->entity;
    }

    public function __toString(): string
    {
        return (string)$this->toAggregate();
    }
}
