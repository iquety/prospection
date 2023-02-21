<?php

declare(strict_types=1);

namespace Tests\EventStore\Support;

use Iquety\Domain\Core\IdentityObject;
use Iquety\PubSub\Event\Event;

class DummyEventCommon extends Event
{
    protected function __construct(
        private IdentityObject $aggregateId,
        private string $one,
        private string $two
    ) {
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
