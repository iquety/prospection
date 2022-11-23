<?php

declare(strict_types=1);

namespace Tests\Domain\Support;

use Iquety\Prospection\Domain\Core\Entity;
use Iquety\Prospection\Domain\Core\IdentityObject;

class Address extends Entity
{
    public function __construct(
        private IdentityObject $identity,
        private string $address,
        private string $number,
        private string $city,
        private int $cep
    ){
    }

    public function identity(): IdentityObject
    {
        return $this->identity;
    }

    // regras de negócio

    public function calculateShipping(int $cepDestination): float
    {
        return 50.33;
    }

    public function distance(int $cepDestination): float
    {
        return 520.1;
    }
}
