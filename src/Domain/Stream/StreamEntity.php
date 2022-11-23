<?php

declare(strict_types=1);

namespace Iquety\Prospection\Domain\Stream;

use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use Iquety\Prospection\Domain\Core\Entity;
use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\EventStore\EventSnapshot;
use Throwable;

/**
 * Esta é a implementação de uma entidade que contém gerenciamento de fluxos
 * de eventos. Deve ser usada para entidades que representem a raiz de uma 
 * agregação destinada ao uso de Event Sourcing.
 */
abstract class StreamEntity extends Entity
{
    /** @var array<int,DomainEvent> */
    private array $listOfChanges = [];

    private ?State $state = null;

    public function aggregateId(): IdentityObject
    {
        return $this->state()->aggregateId();
    }

    /**
     * Devolve a lista de eventos ocorridos após a fabricacao da entidade.
     * Esta lista pode ser usada para transmitir os novos eventos através de um
     * mecanismo de mensageria ou armazená-los no banco de dados.
     *
     * @return array<DomainEvent>
     */
    public function changes(): array
    {
        return $this->listOfChanges;
    }

    /**
     * Aplica o evento na entidade, mudando seu estado.
     * Os eventos aplicados após a consolidação de uma entidade são armazenados em memória 
     * para serem obtidos posteriormente (usando método self::changes()) com a finalidade 
     * de transmití-los (Pub/Sub) ou persistí-los (Event Sourcing).
     */
    public function changeState(DomainEvent $domainEvent): void
    {
        $this->state()->change($domainEvent);
        
        $this->localChange($domainEvent);

        $this->listOfChanges[] = $domainEvent;
    }

    public function createdOn(): DateTimeImmutable
    {
        return $this->state()->createdOn();
    }

    /**
     * Consolida o estado do agregado, aplicando uma lista de eventos ocorridos.
     * Esta lista, geralmente, será obtida de algum banco de dados a fim de 
     * restabelecer o estado atual da entidade.
     * @param array<DomainEvent> $domainEventList
     */
    public function consolidate(array $domainEventList): void
    {
        if ($domainEventList === []) {
            throw new InvalidArgumentException(
                "To consolidate the state of an aggregate, at least one event is required"
            );
        }

        foreach ($domainEventList as $event) {
            $this->state()->change($event);
        }
    }

    /** Fabrica a entidade com base em um evento de Snapshot */
    public static function factory(EventSnapshot $domainEvent): self
    {
        return new self(...$domainEvent->toArray());
    }

    /**
     * Deve devolver a identificação do agregado.
     * Este valor será usado para persistir os eventos do agregado e obtê-los
     * corretamente posteriormente
     */
    abstract public static function label(): string;

    public function updatedOn(): DateTimeImmutable
    {
        return $this->state()->updatedOn();
    }

    // Entity

    public function equalTo(Entity $other): bool
    {
        return $other instanceOf StreamEntity
            && self::label() === $other::label()
            && $this->identity()->value() === $other->identity()->value();
    }

    public function identity(): IdentityObject
    {
        return $this->aggregateId();
    }

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return $this->state()->toArray();
    }

    public function __toString(): string
    {
        return $this->extractStateString($this->toArray());
    }

    // Support

    protected function state(): State
    {
        if ($this->state === null) {
            $this->state = new State($this->stateProperties());

            $this->consolidate([ new EventSnapshot($this->extractStateValues()) ]);
        }

        return $this->state;
    }

    /** Muda o estado local da entidade */
    private function localChange(DomainEvent $domainEvent): void
    {
        $propertyList = $domainEvent->toArray();

        try {
            foreach ($propertyList as $name => $value) {
                $this->$name = $value;
            }
        } catch(Throwable) {
            throw new Exception(
                "State properties for an aggregation root must be 'protected' visibility"
            );
        }
    }
}
