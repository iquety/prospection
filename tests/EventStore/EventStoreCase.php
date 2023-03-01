<?php

declare(strict_types=1);

namespace Tests\EventStore;

use DateTimeImmutable;
use Iquety\Domain\Core\IdentityObject;
use Iquety\Domain\Event\DomainEvent;
use Iquety\Prospection\EventStore\EventStore;
use Iquety\PubSub\Event\Serializer\JsonEventSerializer;
use Tests\TestCase;

class EventStoreCase extends TestCase
{
    protected function persistedEventData(
        string $aggregateLabel,
        string $eventLabel,
        string $aggregateId,
        int $version,
        int $snapshot = 0,
        array $eventData = []
    ): array {
        $now = new DateTimeImmutable("2022-10-10 00:10:10");
        $now = $now->modify("+$version hours");

        if ($aggregateId === '54321+5h') {
            $now = $now->modify("+5 hours");
        }

        return [
            'aggregateId'    => $aggregateId,
            'aggregateLabel' => $aggregateLabel,
            'eventLabel'     => $eventLabel,
            'version'        => $version,
            'snapshot'       => $snapshot,
            'eventData'      => (new JsonEventSerializer())->serialize($eventData),
            'occurredOn'     => $now->format('Y-m-d H:i:s.u')
        ];
    }

    // protected function generateStoredEvents(
    //     EventStore $eventStore,
    //     int $amountAggregates = 5,
    //     int $amountAggregateTypes = 1,
    //     int $amountEvents = 5
    // ): void {

    //     $eventList = [
    //         new class extends DomainEvent {
    //             public function aggregateId(): IdentityObject { return new IdentityObject('123456'); }
    //             public static function aggregateLabel(): string { return 'user'; }
    //             public static function label(): string { return 'user.created'; }
    //         },

    //         new class extends DomainEvent {
    //             public function aggregateId(): IdentityObject { return new IdentityObject('123456'); }
    //             public static function aggregateLabel(): string { return 'category'; }
    //             public static function label(): string { return 'category.created'; }
    //         },

    //         new class extends DomainEvent {
    //             public function aggregateId(): IdentityObject { return new IdentityObject('123456'); }
    //             public static function aggregateLabel(): string { return 'user'; }
    //             public static function label(): string { return 'user.created'; }
    //         }
    //     ];

    //     // $eventStore->store(DummyStreamEntity::label(), DomainEvent $event);

        
    // }
}
