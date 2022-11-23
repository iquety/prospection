<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore;

use Iquety\Prospection\Domain\AggregateRoot;

/**
 * Um descritor contém os dados de um agregado de forma simplificada, para ser
 * alocado dentro de uma coleção consumindo o mínimo de memória possível e
 * garantindo a imutabilidade dos dados recebidos do armazenamento.
 */
class Descriptor
{
    private bool $consolidatedAggregate = false;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Promoção de propriedades ainda não é suportada no PHPMD
     */
    public function __construct(
        private AggregateRoot $entity,
        private EventSnapshot $snapshot
    ) {
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return $this->snapshot->toArray();
    }

    public function toAggregate(): AggregateRoot
    {
        if ($this->consolidatedAggregate === false) {
            $this->entity->consolidate([ $this->snapshot ]);
            $this->consolidatedAggregate = true;
        }

        return $this->entity;
    }

    public function __toString(): string
    {
        return (string)$this->snapshot;
    }
}
