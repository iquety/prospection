<?php

declare(strict_types=1);

namespace Tests\Stream\Support;

use Iquety\Domain\Core\Entity;
use Iquety\Domain\Core\IdentityObject;

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
