<?php

declare(strict_types=1);

namespace Tests\Domain\Core\Support;

use Iquety\Prospection\Domain\Core\ValueObject;

class DummyValue extends ValueObject
{
    public function __construct(private string $myValue)
    {
    }
}
