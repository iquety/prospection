<?php

declare(strict_types=1);

namespace Tests\EventStore\Support;

use Iquety\Prospection\Domain\IdentityObject;
use Iquety\Prospection\Stream\StreamEntity;

class DummyEntityOne extends StreamEntity
{
    protected function __construct(
        private IdentityObject $aggregateId,
        private string $one, // @phpstan-ignore-line
        private string $two, // @phpstan-ignore-line
        private string $thr // @phpstan-ignore-line
    ) {
        # code...
    }

    public function identity(): IdentityObject
    {
        return $this->aggregateId;
    }

    public static function label(): string
    {
        return 'aggregate.one';
    }
}
