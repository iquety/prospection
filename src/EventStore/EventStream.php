<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore;

use Iquety\Prospection\Domain\Stream\DomainEvent;
use RuntimeException;

/**
 * Um fluxo de eventos contém todos os eventos executados em um determinado
 * periodo do tempo.
 */
class EventStream
{
    /** @var array<DomainEvent> */
    private array $eventList = [];

    private int $version = 0;

    public function addEvent(DomainEvent $event, int $version): void
    {
        $this->eventList[] = $event;

        if ($this->version >= $version) {
            throw new RuntimeException(
                "This event cannot be added because it is out of sync"
            );
        }

        $this->version = $version;
    }

    public function count(): int
    {
        return count($this->eventList);
    }

    /**
     * Todos os eventos ocorridos no período
     * @return array<DomainEvent>
     */
    public function events(): array
    {
        return $this->eventList;
    }

    /**
     * Obtém a versão do estado atual do agregado
     */
    public function version(): int
    {
        return $this->version;
    }
}
