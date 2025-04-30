<?php

declare(strict_types=1);

namespace Tests\Domain\Support;

use DateTime;
use DateTimeImmutable;
use Iquety\Prospection\Domain\StateExtraction;

class StateOk
{
    use StateExtraction;

    public function __construct(
        private string $one, // @phpstan-ignore-line
        private int $two, // @phpstan-ignore-line
        private float $three, // @phpstan-ignore-line
        private DateTimeImmutable $four, // @phpstan-ignore-line
        private DateTime $five, // @phpstan-ignore-line
        private DummyValue $six, // @phpstan-ignore-line
        private DummyEntity $seven, // @phpstan-ignore-line
        private DummyEntityRoot $eight, // @phpstan-ignore-line
    ) {
    }

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
