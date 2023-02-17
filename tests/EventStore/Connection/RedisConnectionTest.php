<?php

declare(strict_types=1);

namespace Tests\EventStore\Connection;

use Exception;
use Iquety\Prospection\EventStore\Mysql\MysqlStore;
use Iquety\Prospection\EventStore\Redis\RedisConnection;
use Tests\TestCase;

class RedisConnectionTest extends TestCase
{
    private function connection(): RedisConnection
    {
        return new RedisConnection(
            'iquety-prospection-redis',
            3306,
            'devel',
            'devel'
        );
    }

    private function errorConnection(): RedisConnection
    {
        return new RedisConnection(
            'iquety-prospection-redis XXXXXX',
            3306,
            'devel',
            'devel'
        );
    }

    /** @test */
    public function dummy(): void
    {
        $this->assertTrue(true);
    }

    // /** @test */
    // public function connectionOk(): void
    // {
    //     $connection = $this->connection();

    //     $this->assertSame('', $connection->lastError()->message());
    //     $this->assertSame('', $connection->lastError()->code());
    // }

    // /** @test */
    // public function connectionError(): void
    // {
    //     $connection = $this->errorConnection();

    //     $this->assertStringContainsString(
    //         'SQLSTATE[HY000] [2002] php_network_getaddresses: ' .
    //         'getaddrinfo for iquety-prospection-mysql XXXXXX failed',
    //          $connection->lastError()->message()
    //     );
    //     $this->assertSame('2002', $connection->lastError()->code());
    // }

    // /** @test */
    // public function selectOk(): void
    // {
    //     $connection = $this->connection();

    //     $result = $connection->select('SELECT 123 AS val');

    //     $this->assertCount(1, $result);
    //     $this->assertSame($result[0], ['val' => 123]);
    //     $this->assertSame('', $connection->lastError()->message());
    // }

    // /** @test */
    // public function selectError(): void
    // {
    //     $connection = $this->errorConnection();

    //     $result = $connection->select('SELECT 123 AS val');

    //     $this->assertCount(0, $result);
    //     $this->assertSame($result, []);
    //     $this->assertSame('2002', $connection->lastError()->code());
    // }

    // /** @test */
    // public function executeOk(): void
    // {
    //     $connection = $this->connection();

    //     $result = $connection->execute('SELECT 123 AS val');

    //     $this->assertSame(1, $result);
    //     $this->assertSame('', $connection->lastError()->message());
    // }

    // /** @test */
    // public function executeErrorConnection(): void
    // {
    //     $connection = $this->errorConnection();

    //     $result = $connection->execute('SELECT 123 AS val');

    //     $this->assertSame(0, $result);
    //     $this->assertSame('2002', $connection->lastError()->code());
    // }

    // /** @test */
    // public function executeErrorRuntime(): void
    // {
    //     $connection = $this->connection();

    //     $result = $connection->execute('SELECTSSS 123 AS val');

    //     $this->assertSame(0, $result);
    //     $this->assertStringContainsString(
    //         'SQLSTATE[42000]: Syntax error or access violation: ' .
    //         '1064 You have an error in your SQL syntax',
    //          $connection->lastError()->message()
    //     );
    //     $this->assertSame('42000', $connection->lastError()->code());
    // }

    // /** @test */
    // public function transactionOk(): void
    // {
    //     $connection = $this->connection();

    //     $connection->transaction(
    //         fn() => true,
    //         new MysqlStore($connection, 'events')
    //     );

    //     $this->assertSame('', $connection->lastError()->message());
    // }

    // /** @test */
    // public function transactionErrorConnection(): void
    // {
    //     $connection = $this->errorConnection();

    //     $connection->transaction(
    //         fn() => true,
    //         new MysqlStore($connection, 'events')
    //     );

    //     $this->assertSame('2002', $connection->lastError()->code());
    // }

    // /** @test */
    // public function transactionErrorRuntime(): void
    // {
    //     $connection = $this->connection();

    //     $connection->transaction(
    //         fn() => throw new Exception('x', 777),
    //         new MysqlStore($connection, 'events')
    //     );

    //     $this->assertStringContainsString(
    //         'x',
    //          $connection->lastError()->message()
    //     );
    //     $this->assertSame('777', $connection->lastError()->code());
    // }
}
