<?php

declare(strict_types=1);

namespace Iquety\Prospection\Domain\Core;

use DateTimeInterface;
use Exception;
use ReflectionMethod;
use ReflectionObject;

/**
 * Os objetos de valor não possuem indentidade e são diferenciados pelos seus
 * valores. Um objeto de valor composto, contendo dois valores (ex: 'Nome' e 'Sobrenome'),
 * deve ser comparado levando em conta os dois valores que o compõe.
 */
trait StateExtraction
{
    private ?ReflectionObject $reflection = null;

    private array $stateFields = [];

    protected function reflection(): ReflectionObject
    {
        if ($this->reflection === null) {
            $this->reflection = new ReflectionObject($this);
        }

        return $this->reflection;
    }

    protected function reflectionConstructor(): ReflectionMethod
    {
        $reflection = $this->reflection();

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            throw new Exception(
                'Every object containing state must have a constructor ' .
                'that takes its complete state'
            );
        }

        return $constructor;
    }

    protected function stateProperties(): array
    {
        if ($this->stateFields !== []) {
            return $this->stateFields;
        }

        $constructor = $this->reflectionConstructor();
    
        $this->stateFields = array_map(
            fn($item) => $item->getName(),
            $constructor->getParameters()
        );

        return $this->stateFields;
    }

    /** @return array<string,mixed> */
    protected function extractStateValues(): array
    {
        $reflection = $this->reflection();

        $propertyList = $this->stateProperties();

        $stateValues = [];

        foreach ($propertyList as $label) {
            $property = $reflection->getProperty($label);
            
            $property->setAccessible(true);

            $stateValues[$label] = $property->getValue($this);
        }

        return $stateValues;
    }

    protected function extractStateString(array $stateValues = []): string
    {
        $stateValues = $stateValues !== [] ? $stateValues : $this->extractStateValues();

        return sprintf(
            "%s [%s]",
            $this->reflection()->getShortName(),
            $this->makeStructure($stateValues)
        );
    }

    private function makeStructure(array $stateValues): string
    {
        if (count($stateValues) === 1) {
            return (string)current($stateValues);
        }

        $structure = [];

        foreach ($stateValues as $name => $value) {
            $structure[] = sprintf("%s = %s", $name, $this->valueToString($value));
        }

        return PHP_EOL . "    " . implode(PHP_EOL . "    ", $structure) . PHP_EOL;
    }

    private function valueToString(mixed $value): string
    {
        if ($value instanceof DateTimeInterface) {
            return sprintf(
                "%s %s [%s]",
                $value::class,
                $value->getTimezone()->getName(),
                $value->format('Y-m-d H:i:s.u')
            );
        }

        $string = (string)$value;

        $firstLn = strpos($string, "\n");
        if ($firstLn !== false) {
            $string = substr($string, 0, $firstLn) . "...]";
        }

        return $string;
    }
}
