<?php

declare(strict_types=1);

namespace Tests\Domain\Core\Support;

use DateTime;
use DateTimeImmutable;
use Iquety\Prospection\Domain\Core\StateExtraction;

class StateOk
{
    use StateExtraction;

    public function __construct(
        private string $one,
        private int $two,
        private float $three,
        private DateTimeImmutable $four,
        private DateTime $five,
        private DummyValue $six,
        private DummyEntity $seven,
        private DummyEntityRoot $eight,
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
