<?php

declare(strict_types=1);

namespace Tests\EventStore;

use DateTimeImmutable;
use Iquety\PubSub\Event\Serializer\JsonEventSerializer;
use Tests\TestCase;

class EventStoreCase extends TestCase
{
    protected function persistedEventData(
        string $aggregateLabel,
        string $eventLabel,
        string $id,
        int $version,
        int $snapshot = 0,
        array $eventData = []
    ): array {
        $now = new DateTimeImmutable("2022-10-10 00:10:10");
        $now = $now->modify("+$version hours");

        if ($id === '54321+5h') {
            $now = $now->modify("+5 hours");
        }

        return [
            'aggregateId'    => $id,
            'aggregateLabel' => $aggregateLabel,
            'eventLabel'     => $eventLabel,
            'version'        => $version,
            'snapshot'       => $snapshot,
            'eventData'      => (new JsonEventSerializer())->serialize($eventData),
            'occurredOn'     => $now->format('Y-m-d H:i:s.u') 
        ];
    }
}