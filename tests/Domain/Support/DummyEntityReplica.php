<?php

declare(strict_types=1);

namespace Tests\Domain\Support;

use Iquety\Prospection\Domain\Entity;
use Iquety\Prospection\Domain\IdentityObject;

class DummyEntityReplica extends Entity
{
    public function __construct(
        private IdentityObject $identity,
        private string $myValue // @phpstan-ignore-line
    ) {
    }

    public function identity(): IdentityObject
    {
        return $this->identity;
    }
}
