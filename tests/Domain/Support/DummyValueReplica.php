<?php

declare(strict_types=1);

namespace Tests\Domain\Support;

use Iquety\Prospection\Domain\ValueObject;

class DummyValueReplica extends ValueObject
{
    public function __construct(private string $myValue)
    {
    }
}
