<?php

declare(strict_types=1);

namespace Tests\Domain\Support;

use Iquety\Prospection\Domain\Core\ValueObject;

class Email extends ValueObject
{
    private string $email = '';

    public function __construct(string $email)
    {
        // validar email
        $this->email = $email;
    }

    public function email(): string
    {
        return $this->email;
    }
}
