<?php

declare(strict_types=1);

namespace Tests\Domain\Core\Support;

use Iquety\Prospection\Domain\Core\StateExtraction;

class NoConstructor
{
    use StateExtraction;

    /** @return array<string,mixed> */
    public function extractArray(): array
    {
        return $this->extractStateValues();
    }

    public function extractString(): string
    {
        return $this->extractStateString();
    }
}
