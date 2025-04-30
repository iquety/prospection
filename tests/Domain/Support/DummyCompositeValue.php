<?php

declare(strict_types=1);

namespace Tests\Domain\Support;

use Iquety\Prospection\Domain\ValueObject;

class DummyCompositeValue extends ValueObject
{
    // @phpstan-ignore-next-line
    public function __construct(private string $myValue, private string $other)
    {
    }
}
