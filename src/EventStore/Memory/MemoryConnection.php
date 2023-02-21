<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore\Memory;

use InvalidArgumentException;
use Iquety\Domain\Core\IdentityObject;
use OutOfRangeException;

class MemoryConnection
{
    private static ?MemoryConnection $instance = null;

    /** @var array<int,array<string,mixed>> */
    private array $eventList = [];

    public static function instance(): self
    {
        if (static::$instance === null) { // @phpstan-ignore-line
            static::$instance = new self(); // @phpstan-ignore-line
        }

        return static::$instance; // @phpstan-ignore-line
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

    /** @return array<int,array<string,mixed>> */
    public function all(): array
    {
        return $this->eventList;
    }
}
