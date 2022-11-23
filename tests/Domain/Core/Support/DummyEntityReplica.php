<?php

declare(strict_types=1);

namespace Tests\Domain\Core\Support;

use Iquety\Prospection\Domain\Core\Entity;
use Iquety\Prospection\Domain\Core\IdentityObject;

class DummyEntityReplica extends Entity
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
}
