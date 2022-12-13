<?php

declare(strict_types=1);

namespace Tests\EventStore\Store;

use DateTimeImmutable;
use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\EventStore\Memory\MemoryConnection;
use Iquety\Prospection\EventStore\Store;
use Tests\EventStore\EventStoreCase;
use Tests\EventStore\UseFactories;
use Tests\TestCase;

abstract class AbstractStoreCase extends EventStoreCase
{
    abstract public function getPersistedEvents(): array;

    abstract public function resetDatabase(): void;

    abstract public function storeFactory(): Store;

    /** @test */
    public function addEvent(): void
    {
        $this->resetDatabase();

        $eventData = $this->persistedEventData('aggregate.one', 'user.create', '12345', 1, 1);
        $eventData['occurredOn'] = new DateTimeImmutable($eventData['occurredOn']);

        $this->storeFactory()->add(...$eventData);

        $list = $this->getPersistedEvents();

        $this->assertCount(1, $list);
        $this->assertEquals([
            'aggregateId' => '12345',
            'aggregateLabel' => 'aggregate.one',
            'eventLabel' => 'user.create',
            'version' => 1,
            'snapshot' => 1,
            'eventData' => '{}',
            'occurredOn' => '2022-10-10 01:10:10.000000',
        ], $list[0]);
    }

    /** @test */
    public function remove(): void
    {
        $list = $this->getPersistedEvents();

        $this->assertCount(8, $list);

        $this->assertEquals(1, $list[4]['version']);
        $this->assertEquals('2022-10-10 01:10:10.000000', $list[4]['occurredOn']);
        $this->assertEquals(2, $list[5]['version']);
        $this->assertEquals('2022-10-10 02:10:10.000000', $list[5]['occurredOn']);
        $this->assertEquals(3, $list[6]['version']);
        $this->assertEquals('2022-10-10 03:10:10.000000', $list[6]['occurredOn']);
        $this->assertEquals(4, $list[7]['version']);
        $this->assertEquals('2022-10-10 04:10:10.000000', $list[7]['occurredOn']);

        $this->storeFactory()->remove('aggregate.one', '54321', 2);

        $list = $this->getPersistedEvents();

        $this->assertCount(7, $list);

        $this->assertEquals(1, $list[4]['version']);
        $this->assertEquals('2022-10-10 01:10:10.000000', $list[4]['occurredOn']);
        $this->assertEquals(2, $list[5]['version']); // reindexou a versao 3 -> 2
        $this->assertEquals('2022-10-10 03:10:10.000000', $list[5]['occurredOn']); // ocorrência original
        $this->assertEquals(3, $list[6]['version']); // reindexou a versao 4 -> 3
        $this->assertEquals('2022-10-10 04:10:10.000000', $list[6]['occurredOn']); // ocorrência original
    }

    /** @test */
    public function removePrevious(): void
    {
        $list = $this->getPersistedEvents();

        $this->assertCount(8, $list);

        $this->assertEquals(1, $list[4]['version']);
        $this->assertEquals('2022-10-10 01:10:10.000000', $list[4]['occurredOn']); // ocorrência a 01h10m
        $this->assertEquals(2, $list[5]['version']);
        $this->assertEquals('2022-10-10 02:10:10.000000', $list[5]['occurredOn']);
        $this->assertEquals(3, $list[6]['version']);
        $this->assertEquals('2022-10-10 03:10:10.000000', $list[6]['occurredOn']);
        $this->assertEquals(4, $list[7]['version']);
        $this->assertEquals('2022-10-10 04:10:10.000000', $list[7]['occurredOn']);

        $this->storeFactory()->removePrevious('aggregate.one', '54321', 4);

        $list = $this->getPersistedEvents();

        $this->assertCount(5, $list);

        // ocorrência a 01h, 02h e 03h foram removidos

        $this->assertEquals(1, $list[4]['version']); // reindexou a versao 4 -> 1
        $this->assertEquals('2022-10-10 04:10:10.000000', $list[4]['occurredOn']); // ocorrência original
    }

    /** @test */
    public function removeAll(): void
    {
        $this->assertCount(8, $this->getPersistedEvents());

        $this->storeFactory()->removeAll();

        $this->assertCount(0, $this->getPersistedEvents());
    }

    /** @test */
    public function transactionClosure(): void
    {
        $this->assertCount(8, $this->getPersistedEvents());

        $store = $this->storeFactory();

        $store->transaction(function(Store $store){
            $store->removeAll();
        });

        $this->assertCount(0, $this->getPersistedEvents());
    }
}