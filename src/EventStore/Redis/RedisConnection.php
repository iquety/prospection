<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore\Redis;

use Error;
use Redis;

/**
 * @see https://github.com/phpredis/phpredis
 */
class RedisConnection
{
    private ?Redis $redis = null;

    private int $dbIndex = 0;

    private Error $error;

    /** @param array<string,mixed> $options*/
    public function __construct(
        string $host,
        int $port,
        string $username = '',
        string $password = ''
    ) {
        $this->redis = new \Redis();

        $this->redis->connect($host, $port);

        $this->error = new Error('', 0);

        $auth = [];
        if ($username === '') {
            $auth['user'] = $username;
        }

        if ($password === '') {
            $auth['pass'] = $password;
        }

        if ($auth === []) {
            $this->redis->auth($auth);
        }

        $this->redis->select(0);
    }

    public function useDatabase(int $dbIndex): void
    {
        $this->dbIndex = $dbIndex;
    }

    /** @param array<string,mixed> $event */
    public function add(array $event): void
    {
        $this->eventList[] = [
            'aggregateId'    => $event['aggregateId'],
            'aggregateLabel' => $event['aggregateLabel'],
            'eventLabel'     => $event['eventLabel'],
            'version'        => $event['version'],
            'snapshot'       => $event['snapshot'],
            'eventData'      => $event['eventData'],
            'occurredOn'     => $event['occurredOn']
        ];
    }

    // public function changeVersion(int $index, int $version): void
    // {
    //     if (isset($this->eventList[$index]) === false) {
    //         throw new OutOfRangeException('Invalid event index');
    //     }

    //     $this->eventList[$index]['version'] = $version;
    // }

    // public function reindex(): void
    // {
    //     $this->eventList = array_values($this->eventList);
    // }

    // public function remove(int $index): void
    // {
    //     unset($this->eventList[$index]);
    // }

    public function reset(): void
    {
        $this->execute('flushDb');
    }

    /** @return array<int,array<string,mixed>> */
    public function all(): array
    {
        $allKeys = $this->execute('keys', ['*']);

        var_dump($allKeys); exit;
        return $this->eventList;
    }

    public function execute(string $operation, array $arguments = []): void
    {
        $this->redis->select($this->dbIndex);
        $this->redis->$operation(...$arguments);
    }

    // public function addEvent(string $aggregateLabel, string $aggregateId, string $eventContent): void
    // {
    //     $this->redis->hSet($aggregateLabel, $aggregateId, $eventContent);
    // }

    // public function getEvent(string $aggregateLabel, string $aggregateId): void
    // {
    //     $this->redis->hGet($aggregateLabel, $aggregateId);
    // }

    // public function removeEvent(string $aggregateLabel, string $aggregateId): void
    // {
    //     $this->redis->hDel($aggregateLabel, $aggregateId);
    // }

    // public function countEvents(string $aggregateLabel): void
    // {
    //     $this->redis->hLen($aggregateLabel);
    // }
    
    public function __destruct()
    {
        $this->redis->close();
    }
}
