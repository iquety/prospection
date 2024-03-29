<?php

declare(strict_types=1);

namespace Tests\EventStore\EventHandler\Case;

use DateTimeImmutable;
use Iquety\Domain\Core\IdentityObject;
use Iquety\Prospection\EventStore\EventSnapshot;
use Iquety\Prospection\EventStore\EventStore;
use Tests\EventStore\EventStoreCase;
use Tests\EventStore\Support\DummyEntityOne;
use Tests\EventStore\Support\DummyEntityTwo;
use Tests\EventStore\Support\DummyEventOne;
use Tests\EventStore\Support\DummyEventThr;
use Tests\EventStore\Support\DummyEventTwo;

/** @SuppressWarnings(PHPMD.StaticAccess) */
abstract class AbstractCase extends EventStoreCase
{
    use Consolidation;
    use Counting;
    use Listing;
    use Materialization;
    use Remove;
    use Store;
    use Stream;

    abstract public function getPersistedEvents(): array;

    abstract public function eventStoreFactory(): EventStore;

    abstract public function resetDatabase(): void;

    public function setUp(): void
    {
        $this->resetDatabase();

        $oneFactory  = fn($aggregateId) => $this->aggregateOneListFactory($aggregateId);
        $twoFactory  = fn($aggregateId) => $this->aggregateTwoListFactory($aggregateId);

        $this->eventStoreFactory()->storeMultiple(DummyEntityOne::class, $oneFactory('12345'));
        $this->eventStoreFactory()->storeMultiple(DummyEntityOne::class, $oneFactory('67890'));
        $this->eventStoreFactory()->storeMultiple(DummyEntityOne::class, $oneFactory('abcde'));
        $this->eventStoreFactory()->storeMultiple(DummyEntityOne::class, $oneFactory('fghij'));
        $this->eventStoreFactory()->storeMultiple(DummyEntityOne::class, $oneFactory('tuvxyz'));

        $this->eventStoreFactory()->storeMultiple(DummyEntityTwo::class, $twoFactory('12345'));
        $this->eventStoreFactory()->storeMultiple(DummyEntityTwo::class, $twoFactory('abcde'));
    }

    public function eventStoreProvider(): array
    {
        $simple = $this->eventStoreFactory();

        $eventResolution = $this->eventStoreFactory();
        $eventResolution->registerEventType(DummyEventOne::class);
        $eventResolution->registerEventType(DummyEventTwo::class);

        return [
            [$simple],
            [$eventResolution]
        ];
    }

    private function aggregateOneListFactory(string $aggregateId): array
    {
        return [
            EventSnapshot::factory([
                'aggregateId' => new IdentityObject($aggregateId),
                'one' => 'Fulano',
                'two' => 'Ciclano',
                'thr' => 'Naitis',
                'occurredOn' => new DateTimeImmutable('2022-10-10 00:00:00')
            ]),

            DummyEventOne::factory([
                'aggregateId' => new IdentityObject($aggregateId),
                'one' => 'Ricardo',
                'occurredOn' => new DateTimeImmutable('2022-10-10 01:00:00')
            ]),

            DummyEventTwo::factory([
                'aggregateId' => new IdentityObject($aggregateId),
                'two' => 'Pereira',
                'occurredOn' => new DateTimeImmutable('2022-10-10 02:00:00')
            ])
        ];
    }

    private function aggregateTwoListFactory(string $aggregateId): array
    {
        return [
            EventSnapshot::factory([
                'aggregateId' => new IdentityObject($aggregateId),
                'one' => 'Fulano',
                'two' => 'Ciclano',
                'thr' => 'Naitis',
                'occurredOn' => new DateTimeImmutable('2022-10-10 00:00:00')
            ]),

            DummyEventThr::factory([
                'aggregateId' => new IdentityObject($aggregateId),
                'one' => 'Ricardo',
                'two' => 'Pereira',
                'occurredOn' => new DateTimeImmutable('2022-10-10 01:00:00')
            ])
        ];
    }
}
