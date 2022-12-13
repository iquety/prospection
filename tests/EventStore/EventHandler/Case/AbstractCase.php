<?php

declare(strict_types=1);

namespace Tests\EventStore\EventHandler\Case;

use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\EventStore\EventSnapshot;
use Iquety\Prospection\EventStore\EventStore;
use Tests\EventStore\EventStoreCase;
use Tests\EventStore\Support\DummyEntityOne;
use Tests\EventStore\Support\DummyEntityTwo;
use Tests\EventStore\Support\DummyEventOne;
use Tests\EventStore\Support\DummyEventThr;
use Tests\EventStore\Support\DummyEventTwo;

abstract class AbstractCase extends EventStoreCase
{
    use Consolidation;
    use Counting;
    use Listing;
    use Materialization;
    use Remove;
    use Store;
    
    abstract public function getPersistedEvents(): array;

    abstract public function eventStoreFactory(): EventStore;

    abstract public function resetDatabase(): void;

    public function setUp(): void
    {
        $this->resetDatabase();

        $oneFactory  = fn($id) => $this->aggregateOneListFactory($id);
        $twoFactory  = fn($id) => $this->aggregateTwoListFactory($id);

        $this->eventStoreFactory()->storeMultiple(DummyEntityOne::class, $oneFactory('12345'));
        $this->eventStoreFactory()->storeMultiple(DummyEntityOne::class, $oneFactory('67890'));
        $this->eventStoreFactory()->storeMultiple(DummyEntityOne::class, $oneFactory('abcde'));
        $this->eventStoreFactory()->storeMultiple(DummyEntityOne::class, $oneFactory('fghij'));
        $this->eventStoreFactory()->storeMultiple(DummyEntityOne::class, $oneFactory('tuvxyz'));

        $this->eventStoreFactory()->storeMultiple(DummyEntityTwo::class, $twoFactory('12345'));
        $this->eventStoreFactory()->storeMultiple(DummyEntityTwo::class, $twoFactory('abcde'));
    }

    private function aggregateOneListFactory(string $id): array
    {
        return [
            EventSnapshot::factory([
                'aggregateId' => new IdentityObject($id),
                'one' => 'Fulano',
                'two' => 'Ciclano',
                'thr' => 'Naitis'
            ]),

            DummyEventOne::factory([ 
                'aggregateId' => new IdentityObject($id),
                'one' => 'Ricardo',
            ]),

            DummyEventTwo::factory([ 
                'aggregateId' => new IdentityObject($id),
                'two' => 'Pereira',
            ])
        ];
    }

    private function aggregateTwoListFactory(string $id): array
    {
        return [
            EventSnapshot::factory([
                'aggregateId' => new IdentityObject($id),
                'one' => 'Fulano',
                'two' => 'Ciclano',
                'thr' => 'Naitis'
            ]),

            DummyEventThr::factory([ 
                'aggregateId' => new IdentityObject($id),
                'one' => 'Ricardo',
                'two' => 'Pereira',
            ])
        ];
    }

    // private function eventData(string $id, DateTimeImmutable $occurredOn): array
    // {
    //     return [
    //         "aggregateId" => new IdentityObject($id),
    //         "one" => "Ricardo",
    //         "two" => "Pereira",
    //         "occurredOn" => [
    //             "date" => $occurredOn->format('Y-m-d H:i:s.u'),
    //             "timezone_type" => 3,
    //             "timezone"=>"UTC"
    //         ]
    //     ];
    // }

    // private function eventDataFactory(string $aggregateLabel, string $id, int $version): array
    // {
    //     $now = new DateTimeImmutable("2022-10-10 00:10:10");
    //     $now = $now->modify("+$version hours");

    //     if ($id === '54321') {
    //         $now = $now->modify("+5 hours");
    //     }

    //     $serializer = new JsonEventSerializer();

    //     return [
    //         'aggregateId'    => $id,
    //         'aggregateLabel' => $aggregateLabel,
    //         'eventLabel'     => md5($now->format('Y-m-d H:i:s')),
    //         'version'        => $version,
    //         'snapshot'       => 0,
    //         'eventData'      => $serializer->serialize($this->eventData($id, $now)),
    //         'occurredOn'     => $now->format('Y-m-d H:i:s')
    //     ];
    // }

    // private function snapshotDataFactory(string $aggregateLabel, string $id, int $version): array
    // {
    //     $now = new DateTimeImmutable("2022-10-10 00:10:10");
    //     $now = $now->modify("+$version hours");

    //     if ($id === '54321') {
    //         $now = $now->modify("+5 hours");
    //     }

    //     $serializer = new JsonEventSerializer();
        
    //     return [
    //         'aggregateId'    => $id,
    //         'aggregateLabel' => $aggregateLabel,
    //         'eventLabel'     => EventSnapshot::label(),
    //         'version'        => $version,
    //         'snapshot'       => 1,
    //         'eventData'      => $serializer->serialize($this->eventData($id, $now)),
    //         'occurredOn'     => $now->format('Y-m-d H:i:s')
    //     ];
    // }

    // protected function databaseFactory(): void
    // {
    //     MemoryConnection::instance()->add(
    //         $this->snapshotDataFactory('aggregate.one', '12345', 1)
    //     );

    //     MemoryConnection::instance()->add( // mesmo agregado, id diferente
    //         $this->snapshotDataFactory('aggregate.one', '54321', 1)
    //     );

    //     MemoryConnection::instance()->add( // agregado diferente, mesmo id
    //         $this->snapshotDataFactory('aggregate.two', '12345', 1)
    //     );

    //     MemoryConnection::instance()->add( // tudo diferente
    //         $this->snapshotDataFactory('aggregate.thr', '67890', 1)
    //     );

    //     MemoryConnection::instance()->add( // tudo diferente
    //         $this->eventDataFactory('aggregate.thr', '67890', 2)
    //     );
    // }
}
