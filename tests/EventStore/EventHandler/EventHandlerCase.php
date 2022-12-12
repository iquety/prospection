<?php

declare(strict_types=1);

namespace Tests\EventStore\EventHandler;

use DateTimeImmutable;
use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\EventStore\EventSnapshot;
use Iquety\Prospection\EventStore\EventStore;
use Iquety\Prospection\EventStore\Memory\MemoryConnection;
use Iquety\Prospection\EventStore\Memory\MemoryQuery;
use Iquety\Prospection\EventStore\Memory\MemoryStore;
use Iquety\PubSub\Event\Serializer\JsonEventSerializer;
use Tests\TestCase;

class EventHandlerCase extends TestCase
{
    public function setUp(): void
    {
        MemoryConnection::instance()->reset();
    }

    public function eventStoreFactory(): EventStore
    {
        return new EventStore(
            new MemoryQuery(),
            new MemoryStore(),
            new JsonEventSerializer()
        );
    }

    private function eventData(string $id, DateTimeImmutable $occurredOn): array
    {
        return [
            "aggregateId" => new IdentityObject($id),
            "one" => "Ricardo",
            "two" => "Pereira",
            "occurredOn" => [
                "date" => $occurredOn->format('Y-m-d H:i:s.u'),
                "timezone_type" => 3,
                "timezone"=>"UTC"
            ]
        ];
    }

    private function eventDataFactory(string $aggregateLabel, string $id, int $version): array
    {
        $now = new DateTimeImmutable("2022-10-10 00:10:10");
        $now = $now->modify("+$version hours");

        if ($id === '54321') {
            $now = $now->modify("+5 hours");
        }

        $serializer = new JsonEventSerializer();

        return [
            'aggregateId'    => $id,
            'aggregateLabel' => $aggregateLabel,
            'eventLabel'     => md5($now->format('Y-m-d H:i:s')),
            'version'        => $version,
            'snapshot'       => 0,
            'eventData'      => $serializer->serialize($this->eventData($id, $now)),
            'occurredOn'     => $now->format('Y-m-d H:i:s')
        ];
    }

    private function snapshotDataFactory(string $aggregateLabel, string $id, int $version): array
    {
        $now = new DateTimeImmutable("2022-10-10 00:10:10");
        $now = $now->modify("+$version hours");

        if ($id === '54321') {
            $now = $now->modify("+5 hours");
        }

        $serializer = new JsonEventSerializer();
        
        return [
            'aggregateId'    => $id,
            'aggregateLabel' => $aggregateLabel,
            'eventLabel'     => EventSnapshot::label(),
            'version'        => $version,
            'snapshot'       => 1,
            'eventData'      => $serializer->serialize($this->eventData($id, $now)),
            'occurredOn'     => $now->format('Y-m-d H:i:s')
        ];
    }

    // public function snapshotDataFactory(string $aggregateLabel, string $id, int $version): array
    // {
    //     $now = new DateTimeImmutable("2022-10-10 00:10:10");
    //     $now = $now->modify("+$version hours");

    //     return [
    //         'aggregateId'    => new IdentityObject($id),
    //         'aggregateLabel' => $aggregateLabel,
    //         'eventLabel'     => EventSnapshot::label(),
    //         'version'        => $version,
    //         'snapshot'       => 1,
    //         'eventData'      => json_encode([
    //             "aggregateId" => ["class" => IdentityObject::class,"state" => ["identity"=>$id]],
    //             "one" => "Ricardo",
    //             "two" => "Pereira",
    //             "occurredOn" => ["date" => $now->format('Y-m-d H:i:s.u'),"timezone_type" => 3,"timezone"=>"UTC"]
    //         ]),
    //         'occurredOn'     => $now->format('Y-m-d H:i:s.u')
    //     ];
    // }

    protected function databaseFactory(): void
    {
        MemoryConnection::instance()->add(
            $this->snapshotDataFactory('aggregate.one', '12345', 1)
        );

        MemoryConnection::instance()->add( // mesmo agregado, id diferente
            $this->snapshotDataFactory('aggregate.one', '54321', 1)
        );

        MemoryConnection::instance()->add( // agregado diferente, mesmo id
            $this->snapshotDataFactory('aggregate.two', '12345', 1)
        );

        MemoryConnection::instance()->add( // tudo diferente
            $this->snapshotDataFactory('aggregate.thr', '67890', 1)
        );

        MemoryConnection::instance()->add( // tudo diferente
            $this->eventDataFactory('aggregate.thr', '67890', 2)
        );
    }
}
