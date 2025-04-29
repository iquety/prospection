<?php

declare(strict_types=1);

namespace Tests\Domain\Support;

use Iquety\Prospection\Domain\Entity;
use Iquety\Prospection\Domain\IdentityObject;

class DummyEntity extends Entity
{
    public function __construct(
        private IdentityObject $identity,
        private string $myValue
    ) {
    }

    public function identity(): IdentityObject
    {
        return $this->identity;
    }

    public function myValue(): string
    {
        return $this->myValue;
    }
}
