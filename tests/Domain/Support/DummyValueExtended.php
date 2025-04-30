<?php

declare(strict_types=1);

namespace Tests\Domain\Support;

class DummyValueExtended extends DummyValue
{
    // @phpstan-ignore-next-line
    public function __construct(private string $myValue)
    {
    }
}
