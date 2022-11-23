<?php

declare(strict_types=1);

namespace Iquety\Prospection\Domain\Core;

/**
 * As entidades são diferenciadas através de suas identidades.
 */
abstract class Entity
{
    use StateExtraction;

    public function equalTo(Entity $other): bool
    {
        return $this->identity()->value() === $other->identity()->value();
    }

    abstract public function identity(): IdentityObject;

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return $this->extractStateValues();
    }

    public function __toString(): string
    {
        return $this->extractStateString();
    }
}
