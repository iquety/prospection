<?php

declare(strict_types=1);

namespace Tests\EventStore\Store;

use Iquety\Prospection\EventStore\Error;
use Iquety\Prospection\EventStore\Mysql\MysqlConnection;
use Iquety\Prospection\EventStore\Mysql\MysqlStore;
use Iquety\Prospection\EventStore\Store;

class MysqlStoreTest extends AbstractStoreCase
{
    private function connection(): MysqlConnection
    {
        return new MysqlConnection(
            'devel',
            'iquety-prospection-mysql',
            3306,
            'devel',
            'devel'
        );
    }

    public function getPersistedEvents(): array
    {
        $result = $this->connection()->select('SELECT * FROM events');

        return array_map(function ($item) {
            return [
                'aggregateId'    => $item['aggregate_id'],
                'aggregateLabel' => $item['aggregate_label'],
                'eventLabel'     => $item['event_label'],
                'version'        => $item['version'],
                'snapshot'       => $item['snapshot'],
                'eventData'      => $item['event_data'],
                'occurredOn'     => $item['occurred_on'],
                // 'createdOn'      => $item['created_on'],
                // 'updatedOn'      => $item['updated_on']
            ];
        }, $result);
    }

    public function resetDatabase(): void
    {
        /** @var MysqlStore $object */
        $object = $this->storeFactory();

        $object->removeTable();
        $object->createTable();
    }

    public function storeFactory(): Store
    {
        return new MysqlStore($this->connection(), 'events');
    }

    public function setUp(): void
    {
        $this->resetDatabase();

        $this->persistEvent('12345', 1, 1);
        $this->persistEvent('12345', 2, 0);
        $this->persistEvent('12345', 3, 0);
        $this->persistEvent('12345', 4, 0);

        $this->persistEvent('54321', 1, 1);
        $this->persistEvent('54321', 2, 0);
        $this->persistEvent('54321', 3, 0);
        $this->persistEvent('54321', 4, 0);
    }

    /** @test */
    public function errors(): void
    {
        $object = $this->storeFactory();

        $this->assertFalse($object->hasError());
        $this->assertInstanceOf(Error::class, $object->lastError());
        $this->assertEquals('', $object->lastError()->code());
        $this->assertEquals('', $object->lastError()->message());
    }

    protected function persistEvent(
        string $id,
        int $version,
        int $snapshot
    ): void {
        $eventData = $this->persistedEventData(
            "aggregate.one",
            "event.$version",
            $id,
            $version,
            $snapshot,
            []
        );

        $sql = "
            INSERT INTO events (
                aggregate_id,
                aggregate_label,
                event_label,
                `version`,
                `snapshot`,
                event_data,
                occurred_on
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ";

        $this->connection()->execute($sql, array_values($eventData));
    }
}
