<?php

declare(strict_types=1);

namespace Tests\Stream\Support;

use ArrayObject;
use DateTimeImmutable;
use Iquety\Domain\Core\IdentityObject;
use Iquety\Prospection\Stream\StreamEntity;
use Tests\Stream\Support\DummyEntity;
use Tests\Stream\Support\DummyValue;

class DummyStreamEntity extends StreamEntity
{
    protected function __construct(
        private IdentityObject $aggregateId,
        private string $one,
        private int $two,
        private float $three,
        private DateTimeImmutable $four,
        private ArrayObject $five,
        private DummyValue $six,
        private DummyEntity $seven
    ) {
    }

    public static function label(): string
    {
        return 'aggregado.teste';
    }

    public function one(): string
    {
        return $this->one;
    }

    public function two(): int
    {
        return $this->two;
    }

    public function three(): float
    {
        return $this->three;
    }

    public function four(): DateTimeImmutable
    {
        return $this->four;
    }

    public function five(): ArrayObject
    {
        return $this->five;
    }

    public function six(): DummyValue
    {
        return $this->six;
    }

    public function seven(): DummyEntity
    {
        return $this->seven;
    }
}
