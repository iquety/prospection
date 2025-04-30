<?php

declare(strict_types=1);

namespace Tests\Stream\Support;

use ArrayObject;
use DateTimeImmutable;
use Iquety\Prospection\Domain\IdentityObject;
use Tests\Stream\Support\DummyEntity;
use Tests\Stream\Support\DummyValue;

class DummyStreamEntityOtherLabel extends DummyStreamEntity
{
    /** @param ArrayObject<int,string> $five */
    protected function __construct(
        private IdentityObject $aggregateId, // @phpstan-ignore-line
        private string $one, // @phpstan-ignore-line
        private int $two, // @phpstan-ignore-line
        private float $three, // @phpstan-ignore-line
        private DateTimeImmutable $four, // @phpstan-ignore-line
        private ArrayObject $five, // @phpstan-ignore-line
        private DummyValue $six, // @phpstan-ignore-line
        private DummyEntity $seven // @phpstan-ignore-line
    ) {
    }

    public static function label(): string
    {
        return 'aggregado.teste.custom';
    }
}
