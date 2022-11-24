<?php

declare(strict_types=1);

namespace Tests\Domain\Stream\Support;

use DateTime;
use DateTimeImmutable;
use Iquety\Prospection\Domain\Core\IdentityObject;
use Tests\Domain\Core\Support\DummyEntity;
use Tests\Domain\Core\Support\DummyValue;

class DummyStreamEntitySameLabel extends DummyStreamEntity
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
}
