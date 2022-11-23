<?php

declare(strict_types=1);

namespace Tests\Domain\Support;

use Iquety\Prospection\Domain\Core\ValueObject;

class Name extends ValueObject
{
    private string $name = '';

    public function __construct(string $name)
    {
        // validar name
        $this->name = $name;
    }
    
    public function name(): string
    {
        return $this->name;
    }
}
