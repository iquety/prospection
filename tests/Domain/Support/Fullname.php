<?php

declare(strict_types=1);

namespace Tests\Domain\Support;

use DateTimeImmutable;
use Iquety\Prospection\Domain\Core\ValueObject;

class Fullname extends ValueObject
{
    private string $lastname = '';

    public function __construct(
        private Name $name,
        string $lastname,
        private DateTimeImmutable $birthday
    ) {
        // validar lastname
        $this->lastname = $lastname;
    }

    public function name(): Name
    {
        return $this->name;
    }
    
    public function lastname(): string
    {
        return $this->lastname;
    }

    public function birthday(): DateTimeImmutable
    {
        return $this->birthday;
    }
}
