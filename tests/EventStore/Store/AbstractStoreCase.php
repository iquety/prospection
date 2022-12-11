<?php

declare(strict_types=1);

namespace Tests\EventStore\Store;

use DateTimeImmutable;
use Exception;
use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\EventStore\Memory\MemoryConnection;
use Iquety\Prospection\EventStore\Store;
use RuntimeException;
use Tests\TestCase;

abstract class AbstractStoreCase extends TestCase
{
    abstract public function storeFactory(): Store;

    /** @test */
    public function addEvent(): void
    {
        MemoryConnection::instance()->reset();

        $this->storeFactory()->add(
            new IdentityObject('12345'),
            'aggregate.one',
            'user.create',
            1,
            1,
            json_encode([]),
            new DateTimeImmutable('2022-10-10 10:10:10')
        );

        $list = MemoryConnection::instance()->all();

        $this->assertCount(1, $list);
        $this->assertEquals([
            'aggregateId' => new IdentityObject('12345'),
            'aggregateLabel' => 'aggregate.one',
            'eventLabel' => 'user.create',
            'version' => 1,
            'snapshot' => 1,
            'eventData' => '[]',
            'occurredOn' => new DateTimeImmutable('2022-10-10 10:10:10.000000'),
        ], $list[0]);
    }

    /** @test */
    public function remove(): void
    {
        $list = MemoryConnection::instance()->all();

        $this->assertCount(8, $list);

        $this->assertEquals(1, $list[4]['version']);
        $this->assertEquals('2022-10-10 01:10:10', $list[4]['occurredOn']->format('Y-m-d H:i:s'));
        $this->assertEquals(2, $list[5]['version']);
        $this->assertEquals('2022-10-10 02:10:10', $list[5]['occurredOn']->format('Y-m-d H:i:s'));
        $this->assertEquals(3, $list[6]['version']);
        $this->assertEquals('2022-10-10 03:10:10', $list[6]['occurredOn']->format('Y-m-d H:i:s'));
        $this->assertEquals(4, $list[7]['version']);
        $this->assertEquals('2022-10-10 04:10:10', $list[7]['occurredOn']->format('Y-m-d H:i:s'));

        $this->storeFactory()->remove('aggregate.one', new IdentityObject('54321'), 2);

        $list = MemoryConnection::instance()->all();

        $this->assertCount(7, $list);

        $this->assertEquals(1, $list[4]['version']);
        $this->assertEquals('2022-10-10 01:10:10', $list[4]['occurredOn']->format('Y-m-d H:i:s'));
        $this->assertEquals(2, $list[5]['version']); // reindexou a versao 3 -> 2
        $this->assertEquals('2022-10-10 03:10:10', $list[5]['occurredOn']->format('Y-m-d H:i:s')); // ocorrência original
        $this->assertEquals(3, $list[6]['version']); // reindexou a versao 4 -> 3
        $this->assertEquals('2022-10-10 04:10:10', $list[6]['occurredOn']->format('Y-m-d H:i:s')); // ocorrência original
    }

    /** @test */
    public function removePrevious(): void
    {
        $list = MemoryConnection::instance()->all();

        $this->assertCount(8, $list);

        $this->assertEquals(1, $list[4]['version']);
        $this->assertEquals('2022-10-10 01:10:10', $list[4]['occurredOn']->format('Y-m-d H:i:s')); // ocorrência a 01h10m
        $this->assertEquals(2, $list[5]['version']);
        $this->assertEquals('2022-10-10 02:10:10', $list[5]['occurredOn']->format('Y-m-d H:i:s'));
        $this->assertEquals(3, $list[6]['version']);
        $this->assertEquals('2022-10-10 03:10:10', $list[6]['occurredOn']->format('Y-m-d H:i:s'));
        $this->assertEquals(4, $list[7]['version']);
        $this->assertEquals('2022-10-10 04:10:10', $list[7]['occurredOn']->format('Y-m-d H:i:s'));

        $this->storeFactory()->removePrevious('aggregate.one', new IdentityObject('54321'), 4);

        $list = MemoryConnection::instance()->all();

        $this->assertCount(5, $list);

        // ocorrência a 01h, 02h e 03h foram removidos

        $this->assertEquals(1, $list[4]['version']); // reindexou a versao 4 -> 1
        $this->assertEquals('2022-10-10 04:10:10', $list[4]['occurredOn']->format('Y-m-d H:i:s')); // ocorrência original
    }

    /** @test */
    public function removeAll(): void
    {
        $this->assertCount(8, MemoryConnection::instance()->all());

        $this->storeFactory()->removeAll();

        $this->assertCount(0, MemoryConnection::instance()->all());
    }

    /** @test */
    public function transactionClosure(): void
    {
        $this->assertCount(8, MemoryConnection::instance()->all());

        $store = $this->storeFactory();

        $store->transaction(function(Store $store){
            $store->removeAll();
        });

        $this->assertCount(0, MemoryConnection::instance()->all());
    }
}