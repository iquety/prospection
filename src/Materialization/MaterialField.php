<?php

declare(strict_types=1);

namespace Iquety\Prospection\Materialization;

use OutOfRangeException;

class MaterialField
{
    public const DECIMAL_PLACES = 'places';
    public const LENGTH         = 'length';
    public const TIMEZONE       = 'timezone';
    public const TYPE           = 'type';
    
    public const SORTING_BY         = 'sorting_by';
    public const SORTING_ASCENDANCY = 'ascendancy';
    public const SORTING_DESCENT    = 'descent';

    public const TYPE_BINARY        = 'binary';
    public const TYPE_DATE          = 'date';
    public const TYPE_DATETIME      = 'datetime';
    public const TYPE_DECIMAL       = 'decimal';
    public const TYPE_FOREIGN_FIELD = 'foreign_field';
    public const TYPE_FOREIGN_KEY   = 'foreign_key';
    public const TYPE_HOUR          = 'hour';
    public const TYPE_IDENTITY      = 'identity';
    public const TYPE_INTEGER       = 'integer';
    public const TYPE_SORTING       = 'sorting';
    public const TYPE_STATE_CONTROL = 'state_control';
    public const TYPE_STRING        = 'string';
    public const TYPE_TEXT          = 'text';

    private AggregateField $aggregateField;

    /** @var array<string,mixed> */
    private array $settings = [];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Promoção de propriedades ainda não é suportada no PHPMD
     */
    public function __construct(private string $materialName)
    {
        $this->aggregateField = new AggregateField('unknown');
    }

    public function fromAggregate(string $stateField, string $stateChildField = ''): self
    {
        $this->aggregateField = new AggregateField($stateField, $stateChildField);

        return $this;
    }

    public function config(string $parameter, string $value): self
    {
        $this->settings[$parameter] = $value;

        return $this;
    }

    public function hasValue(): bool
    {
        return $this->aggregateField->stateField() !== 'unknown';
    }

    public function hasConfig(string $parameter): bool
    {
        return isset($this->settings[$parameter]);
    }

    public function getConfig(string $parameter): string
    {
        return $this->settings[$parameter]
            ?? throw new OutOfRangeException("Configuration parameter '$parameter' does not exist");
    }

    public function name(): string
    {
        return $this->materialName;
    }

    public function mapping(): AggregateField
    {
        return $this->aggregateField;
    }
}
