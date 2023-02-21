<?php

declare(strict_types=1);

namespace Tests\EventStore\Support;

use Iquety\Domain\Core\IdentityObject;
use Iquety\Domain\Event\DomainEvent;

class DummyEventOne extends DomainEvent
{
    protected function __construct(
        private IdentityObject $aggregateId,
        private string $one
    ) {
        # code...
    }

    public function aggregateId(): IdentityObject
    {
        return $this->aggregateId;
    }

    public static function aggregateLabel(): string
    {
        return 'aggregate.one';
    }

    public static function label(): string
    {
        return 'event.one';
    }
}
