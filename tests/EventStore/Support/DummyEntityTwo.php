<?php

declare(strict_types=1);

namespace Tests\EventStore\Support;

use Iquety\Domain\Core\IdentityObject;
use Iquety\Prospection\Stream\StreamEntity;

class DummyEntityTwo extends StreamEntity
{
    protected function __construct(
        private IdentityObject $aggregateId,
        private string $one,
        private string $two,
        private string $thr
    ) {
        # code...
    }

    public function identity(): IdentityObject
    {
        return $this->aggregateId;
    }

    public static function label(): string
    {
        return 'aggregate.two';
    }
}
