<?php

declare(strict_types=1);

namespace Iquety\Prospection\Materialization;

use InvalidArgumentException;
use Iquety\Prospection\EventStore\Descriptor;

class AggregateField
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * Promoção de propriedades ainda não é suportada no PHPMD
     */
    public function __construct(
        private string $name,
        private string $child = ''
    ) {
    }

    public function stateChildField(): string
    {
        return $this->child;
    }

    public function extractState(Descriptor $descriptor): int|float|string
    {
        $stateData = $descriptor->toPrimitives();

        if (isset($stateData[$this->name]) === false) {
            throw new InvalidArgumentException(sprintf(
                "The specified descriptor does not contain the value '%s'",
                $this->name
            ));
        }

        if (is_array($stateData[$this->name]) === false) {
            return $stateData[$this->name];
        }

        if (isset($stateData[$this->name][$this->child]) === false) {
            throw new InvalidArgumentException(sprintf(
                "The specified descriptor does not contain the child value '%s' for the value '%s'",
                $this->child,
                $this->name
            ));
        }

        return $stateData[$this->name][$this->child];
    }

    public function stateField(): string
    {
        return $this->name;
    }
}
