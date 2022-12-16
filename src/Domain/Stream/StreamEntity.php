<?php

declare(strict_types=1);

namespace Iquety\Prospection\Domain\Stream;

use DateTimeImmutable;
use DomainException;
use Exception;
use Iquety\Prospection\Domain\Core\Entity;
use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\EventStore\EventSnapshot;
use Throwable;

/**
 * Esta é a implementação de uma entidade que contém gerenciamento de fluxos
 * de eventos. Deve ser usada para entidades que representem a raiz de uma
 * agregação destinada ao uso de Event Sourcing.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
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
        $this->applyStateChange($domainEvent);

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
            return;
        }

        foreach ($domainEventList as $event) {
            $this->applyStateChange($event);
        }
    }

    /** Fabrica a entidade com base em um evento de Snapshot */
    public static function factory(array $stateValues): self
    {
        $constructorState = $stateValues;

        if (isset($constructorState['occurredOn']) === true) {
            unset($constructorState['occurredOn']);
        }

        try {
            $instance = new static(...$constructorState);
        } catch (Throwable $exception) {
            throw new DomainException($exception->getMessage());
        }

        // chamado aqui para verificar a visibilidade do construtor
        $state = $instance->state();

        if (isset($stateValues['occurredOn']) === true) {
            $state->internalFactoryDateTime($stateValues['occurredOn']);
        }

        return $instance;
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
        return $other instanceof StreamEntity
            && static::label() === $other::label()
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

    public function toSnapshot(): EventSnapshot
    {
        return $this->state()->toSnapshot();
    }

    public function __toString(): string
    {
        return $this->extractStateString($this->toArray());
    }

    // Support

    private function applyStateChange(DomainEvent $domainEvent): void
    {
        $this->state()->change($domainEvent);

        $this->localChange($domainEvent);
    }

    private function checkConstructorVisibility(): void
    {
        if ($this->reflectionConstructor()->isPublic() === true) {
            throw new DomainException(
                "Constructors of objects of type 'StreamEntity' must be protected"
            );
        }
    }

    /** Muda o estado local da entidade */
    private function localChange(DomainEvent $domainEvent): void
    {
        $propertyList = $domainEvent->toArray();

        foreach ($propertyList as $name => $value) {
            if ($name === 'occurredOn') {
                continue;
            }

            $property = $this->reflection()->getProperty($name);
            $property->setAccessible(true);
            $property->setValue($this, $value);
        }
    }

    protected function state(): State
    {
        if ($this->state === null) {
            $this->checkConstructorVisibility();

            $this->state = new State($this->stateProperties());

            $this->consolidate([ new EventSnapshot($this->extractStateValues()) ]);
        }

        return $this->state;
    }
}
