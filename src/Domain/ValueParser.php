<?php

declare(strict_types=1);

namespace Iquety\Prospection\Domain;

use DateTimeImmutable;
use OutOfBoundsException;
use RuntimeException;

class ValueParser
{
    public function __construct(private mixed $value)
    {
    }

    public function hasState(): bool
    {
        return $this->isEntity() === true || $this->isValueObject() === true;
    }

    public function isDatetime(): bool
    {
        if ($this->isObject() === false) {
            return false;
        }

        return $this->value instanceof DateTimeImmutable;
    }

    public function isEntity(): bool
    {
        if ($this->isObject() === false) {
            return false;
        }

        /** @var object */
        $object = $this->value;

        if (method_exists($object, 'identity') === false) {
            return false;
        }

        return true;
    }

    public function isObject(): bool
    {
        if (gettype($this->value) !== 'object') {
            return false;
        }

        return true;
    }

    public function isPrimitive(): bool
    {
        return in_array(gettype($this->value), ['string', 'integer', 'double', 'array', 'boolean']);
    }

    public function isValueObject(): bool
    {
        if ($this->isObject() === false) {
            return false;
        }

        /** @var object */
        $object = $this->value;

        if (method_exists($object, 'value') === false) {
            return false;
        }

        return true;
    }

    public function toPrimitives(): array
    {
        if (is_array($this->value) === true) {
            return $this->convertToPrimitives($this->value);
        }

        if ($this->hasState() === true) {
            return $this->convertToPrimitives($this->value->toArray());
        }

        if ($this->isPrimitive() === true) {
            return $this->value;
        }

        if ($this->isDatetime() === true) {
            return $this->value->format('Y-m-d H:i:s.u');
        }

        throw new RuntimeException('Only stateful or DateTimeImmutable values can be converted to primitives');
    }

    private function convertToPrimitives(array $state): mixed
    {
        foreach($state as $param => $value) {
            if (is_array($value) === true) {
                $state[$param] = $this->convertToPrimitives($value);
                
                continue;
            }

            $parser = new ValueParser($value);

            if ($parser->isDatetime() === true) {
                $state[$param] = $value->format('Y-m-d H:i:s.u');

                continue;
            }

            if ($parser->hasState() === false) {
                continue;
            }

            $valueState = $value->toArray();

            if (count($valueState) === 1) {
                $state[$param] = $value->value();
                
                continue;
            }

            $state[$param] = $this->convertToPrimitives($value->toArray());
        }

        return $state;
    }
}
