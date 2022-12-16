<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore;

class Error
{
    public function __construct(private string $code, private string $message)
    {
    }

    public function message(): string
    {
        return $this->message;
    }

    public function code(): string
    {
        return $this->code;
    }
}
