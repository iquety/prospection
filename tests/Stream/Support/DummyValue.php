<?php

declare(strict_types=1);

namespace Tests\Stream\Support;

use Iquety\Domain\Core\ValueObject;

class DummyValue extends ValueObject
{
    public function __construct(private string $myValue)
    {
    }
}
