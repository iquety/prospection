<?php

declare(strict_types=1);

namespace Tests\Domain\Stream\Support;

use DateTime;
use DateTimeImmutable;
use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\Domain\Stream\StreamEntity;
use Tests\Domain\Core\Support\DummyEntity;
use Tests\Domain\Core\Support\DummyValue;

class DummyStreamEntity extends StreamEntity
{
    protected function __construct(
        private IdentityObject $aggregateId,
        private string $one,
        private int $two,
        private float $three,
        private DateTimeImmutable $four,
        private DateTime $five,
        private DummyValue $six,
        private DummyEntity $seven
    ){
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

    public function five(): DateTime
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
