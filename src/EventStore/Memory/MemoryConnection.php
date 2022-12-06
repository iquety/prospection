<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore\Memory;

use OutOfRangeException;

class MemoryConnection
{
    private static ?MemoryConnection $instance = null;

    private array $eventList = [];

    public static function instance(): self
    {
        if(static::$instance === null) {
            static::$instance = new self();
        }

        return static::$instance;
    }

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

    public function changeVersion(int $index, int $version): void
    {
        if (isset($this->eventList[$index]) === false) {
            throw new OutOfRangeException('Invalid event index');
        }

        $this->eventList[$index]['version'] = $version;
    }

    public function reindex(): void
    {
        $this->eventList = array_values($this->eventList);
    }

    public function remove(int $index): void
    {
        unset($this->eventList[$index]);
    }

    public function reset(): void
    {
        $this->eventList = [];
    }

    public function all(): array
    {
        return $this->eventList;
    }
}
