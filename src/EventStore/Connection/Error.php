<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore\Connection;

class Error
{
    public function __construct(private string $code, private string $message)
    {
    }
    
    public function message(): string
    {
        return '';
    }

    public function code(): string
    {
        return '';
    }
}