<?php

declare(strict_types=1);

namespace Tests\EventStore\Query;

use Iquety\Prospection\EventStore\Error;
use Iquety\Prospection\EventStore\Mysql\MysqlConnection;
use Iquety\Prospection\EventStore\Mysql\MysqlQuery;
use Iquety\Prospection\EventStore\Query;
use Tests\EventStore\Query\Case\AbstractCase;

class MysqlQueryTest extends AbstractCase
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

    public function queryFactory(): Query
    {
        return new MysqlQuery($this->connection(), 'events');
    }

    public function resetDatabase(): void
    {
        $this->connection()->execute('DROP TABLE IF EXISTS events');

        $this->connection()->execute("
            CREATE TABLE IF NOT EXISTS `events` (
                aggregate_id VARCHAR(36) NOT NULL,
                aggregate_label VARCHAR(155) NOT NULL,
                event_label VARCHAR(155) NOT NULL,
                version INT(11) NOT NULL COMMENT 'Estado atual do agregado',
                snapshot INT(1) NOT NULL COMMENT 'Sinalizador de um estado completo',
                event_data TEXT NOT NULL COMMENT 'Dados serializados do evento',
                occurred_on DATETIME(6) NOT NULL COMMENT 'O momento que o evento aconteceu',
                PRIMARY KEY (aggregate_label, aggregate_id, `version`)
            ) ENGINE=InnoDB;
        ");
    }

    public function setUp(): void
    {
        $this->resetDatabase();

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // + 1 snapshot para cada agregado
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $version = 1;

        $this->persistEvent('aggregate.one', '12345', $version, 1);
        $this->persistEvent('aggregate.one', '54321+5h', $version, 1); // id diferente
        $this->persistEvent('aggregate.one', 'abcde', $version, 1);
        $this->persistEvent('aggregate.one', 'fghij', $version, 1);
        $this->persistEvent('aggregate.one', 'klmno', $version, 1);
        $this->persistEvent('aggregate.two', '12345', $version, 1); // aggregado diferente
        $this->persistEvent('aggregate.thr', '67890', $version, 1); // tudo diferente

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // + 9 eventos para cada agregado
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        array_map(function () use (&$version) {
            $version++;

            $this->persistEvent('aggregate.one', '12345', $version, 0);
            $this->persistEvent('aggregate.one', '54321+5h', $version, 0);
            $this->persistEvent('aggregate.two', '12345', $version, 0);
            $this->persistEvent('aggregate.thr', '67890', $version, 0);
        }, range(1, 9));

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // + 1 snapshot para o terceiro agregado
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        $version++;

        $this->persistEvent('aggregate.thr', '67890', $version, 1);

        // - - - - - - - - - - - - - - - - - - - - - - - - - - - -
        // + 5 eventos para o terceiro agregado
        // - - - - - - - - - - - - - - - - - - - - - - - - - - - -

        array_map(function () use (&$version) {
            $version++;

            $this->persistEvent('aggregate.thr', '67890', $version, 0);
        }, range(1, 5));
    }

    protected function persistEvent(
        string $aggregateLabel,
        string $id,
        int $version,
        int $snapshot
    ): void {
        $eventData = $this->persistedEventData(
            $aggregateLabel,
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

    /** @test */
    public function connectionOk(): void
    {
        $object = $this->queryFactory();

        $this->assertFalse($object->hasError());
        $this->assertInstanceOf(Error::class, $object->lastError());
        $this->assertEquals('', $object->lastError()->code());
        $this->assertEquals('', $object->lastError()->message());
    }

    /** @test */
    public function connectionError(): void
    {
        $connection = new MysqlConnection(
            'devel',
            'invalid', // host errado
            3306,
            'devel',
            'devel'
        );

        $object = new MysqlQuery($connection, 'events');

        $this->assertTrue($object->hasError());
        $this->assertInstanceOf(Error::class, $object->lastError());
        $this->assertEquals('2002', $object->lastError()->code());
        $this->assertStringContainsString(
            'SQLSTATE[HY000] [2002] php_network_getaddresses',
            $object->lastError()->message()
        );
    }

    /** @test */
    public function connectionQueryError(): void
    {
        $connection = new MysqlConnection(
            'devel',
            'iquety-prospection-mysql',
            3306,
            'devel',
            'devel'
        );

        $object = new MysqlQuery($connection, 'not_exists');

        $object->countEvents();

        $this->assertTrue($object->hasError());
        $this->assertInstanceOf(Error::class, $object->lastError());
        $this->assertEquals('42S02', $object->lastError()->code());
        $this->assertStringContainsString(
            'SQLSTATE[42S02]: Base table or view not found: 1146',
            $object->lastError()->message()
        );
    }
}
