<?php

declare(strict_types=1);

namespace Tests\EventStore\Store;

use Iquety\Prospection\EventStore\Error;
use Iquety\Prospection\EventStore\Memory\RedisStore;
use Iquety\Prospection\EventStore\Mysql\MysqlConnection;
use Iquety\Prospection\EventStore\Mysql\MysqlStore;
use Iquety\Prospection\EventStore\Redis\RedisConnection;
use Iquety\Prospection\EventStore\Store;
use Tests\TestCase;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class RedisStoreTest extends TestCase
{
    /** @test */
    public function dummy(): void
    {
        $this->assertTrue(true);
    }
    
    // private function connection(): RedisConnection
    // {
    //     return new RedisConnection(
    //         'iquety-prospection-redis',
    //         6379,
    //         '',
    //         ''
    //     );
    // }

    // public function getPersistedEvents(): array
    // {
    //     $result = $this->connection()->select('SELECT * FROM events');

    //     return array_map(function ($item) {
    //         return [
    //             'aggregateId'    => $item['aggregate_id'],
    //             'aggregateLabel' => $item['aggregate_label'],
    //             'eventLabel'     => $item['event_label'],
    //             'version'        => $item['version'],
    //             'snapshot'       => $item['snapshot'],
    //             'eventData'      => $item['event_data'],
    //             'occurredOn'     => $item['occurred_on'],
    //             // 'createdOn'      => $item['created_on'],
    //             // 'updatedOn'      => $item['updated_on']
    //         ];
    //     }, $result);
    // }

    // public function resetDatabase(): void
    // {
    //     $this->connection();

        
    //     var_dump('cxxxx'); exit;
    //     /** @var MysqlStore $object */
    //     $object = $this->storeFactory();

    //     $object->removeTable();
    //     $object->createTable();
    // }

    // public function storeFactory(): Store
    // {
    //     return new RedisStore($this->connection(), 0);
    // }

    // public function setUp(): void
    // {
    //     $this->resetDatabase();

    //     $this->persistEvent('12345', 1, 1);
    //     $this->persistEvent('12345', 2, 0);
    //     $this->persistEvent('12345', 3, 0);
    //     $this->persistEvent('12345', 4, 0);

    //     $this->persistEvent('54321', 1, 1);
    //     $this->persistEvent('54321', 2, 0);
    //     $this->persistEvent('54321', 3, 0);
    //     $this->persistEvent('54321', 4, 0);
    // }

    // /** @test */
    // public function errors(): void
    // {
    //     $object = $this->storeFactory();

    //     $this->assertFalse($object->hasError());
    //     $this->assertInstanceOf(Error::class, $object->lastError());
    //     $this->assertEquals('', $object->lastError()->code());
    //     $this->assertEquals('', $object->lastError()->message());
    // }

    // protected function persistEvent(
    //     string $aggregateId,
    //     int $version,
    //     int $snapshot
    // ): void {
    //     $eventData = $this->persistedEventData(
    //         "aggregate.one",
    //         "event.$version",
    //         $aggregateId,
    //         $version,
    //         $snapshot,
    //         []
    //     );

    //     $sql = "
    //         INSERT INTO events (
    //             aggregate_id,
    //             aggregate_label,
    //             event_label,
    //             `version`,
    //             `snapshot`,
    //             event_data,
    //             occurred_on
    //         ) VALUES (?, ?, ?, ?, ?, ?, ?)
    //     ";

    //     $this->connection()->execute(
    //         'insert',
    //         array_values($eventData)
    //     );
    // }
}
