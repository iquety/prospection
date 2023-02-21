<?php

declare(strict_types=1);

namespace Tests\Stream\Support;

use ArrayObject;
use DateTime;
use DateTimeImmutable;
use Iquety\Domain\Core\IdentityObject;
use Tests\Stream\Support\DummyEntity;
use Tests\Stream\Support\DummyValue;

class DummyStreamEntityOtherLabel extends DummyStreamEntity
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
        return 'aggregado.teste.custom';
    }
}
