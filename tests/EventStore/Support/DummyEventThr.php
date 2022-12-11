<?php

declare(strict_types=1);

namespace Tests\EventStore\Support;

use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\Domain\Stream\DomainEvent;

class DummyEventThr extends DomainEvent
{
    protected function __construct(
        private IdentityObject $aggregateId,
        private string $one,
        private string $two
    )
    {
        # code...
    }

    public function aggregateId(): IdentityObject
    {
        return $this->aggregateId;
    }

    public static function aggregateLabel(): string
    {
        return 'aggregate.two';
    }

    public static function label(): string
    {
        return 'event.thr';
    }
}
