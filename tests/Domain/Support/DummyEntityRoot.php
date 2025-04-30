<?php

declare(strict_types=1);

namespace Tests\Domain\Support;

use Iquety\Prospection\Domain\Entity;
use Iquety\Prospection\Domain\IdentityObject;

class DummyEntityRoot extends Entity
{
    public function __construct(
        private IdentityObject $identity,
        private string $simple, // @phpstan-ignore-line
        private DummyEntity $entity, // @phpstan-ignore-line
        private DummyValue $value // @phpstan-ignore-line
    ) {
    }

    public function identity(): IdentityObject
    {
        return $this->identity;
    }

    // public function simple(): string
    // {
    //     return $this->simple;
    // }

    // public function entity(): DummyEntity
    // {
    //     return $this->entity;
    // }

    // public function value(): DummyValue
    // {
    //     return $this->value;
    // }
}
