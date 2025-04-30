<?php

declare(strict_types=1);

namespace Tests\EventStore\Support;

use Iquety\Prospection\Domain\IdentityObject;
use Iquety\PubSub\Event\Event;

class DummyEventCommon extends Event
{
    protected function __construct(
        private IdentityObject $aggregateId,
        private string $one, // @phpstan-ignore-line
        private string $two // @phpstan-ignore-line
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
