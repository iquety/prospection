<?php

declare(strict_types=1);

namespace Iquety\Prospection\EventStore\Memory;

use Closure;
use DateTimeImmutable;
use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\EventStore\Error;
use Iquety\Prospection\EventStore\Store;

class MemoryStore implements Store
{
    private Error $error;

    public function __construct()
    {
        $this->error = new Error('', '');
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    private function connection(): MemoryConnection
    {
        return MemoryConnection::instance();
    }

    public function add(
        string $aggregateId,
        string $aggregateLabel,
        string $eventLabel,
        int $version,
        int $snapshot,
        string $eventData,
        DateTimeImmutable $occurredOn
    ): void {
        $this->connection()->add([
            'aggregateId'    => $aggregateId,
            'aggregateLabel' => $aggregateLabel,
            'eventLabel'     => $eventLabel,
            'version'        => $version,
            'snapshot'       => $snapshot,
            'eventData'      => $eventData,
            'occurredOn'     => $occurredOn->format('Y-m-d H:i:s.u')
        ]);
    }

    public function hasError(): bool
    {
        return $this->error->message() !== '';
    }

    public function lastError(): Error
    {
        return $this->error;
    }

    /**
     * Remove o evento especificado.
     * @todo Implementar restabelecimento da numeração de versões
     */
    public function remove(
        string $aggregateLabel,
        string $aggregateId,
        int $version
    ): void {
        $list = $this->connection()->all();

        foreach ($list as $index => $register) {
            if (
                $register['aggregateLabel'] === $aggregateLabel
                && $register['aggregateId'] === $aggregateId
                && $register['version'] === $version
            ) {
                $this->connection()->remove($index);
                break;
            }
        }

        $this->connection()->reindex();

        $this->sortVersions($aggregateLabel, $aggregateId);
    }

    /**
     * Remove o evento especificado.
     * @todo Implementar restabelecimento da numeração de versões
     */
    public function removePrevious(
        string $aggregateLabel,
        string $aggregateId,
        int $version
    ): void {
        $list = $this->connection()->all();

        foreach ($list as $index => $register) {
            if (
                $register['aggregateLabel'] === $aggregateLabel
                && $register['aggregateId'] === $aggregateId
                && $register['version'] < $version
            ) {
                $this->connection()->remove($index);
            }
        }

        $this->connection()->reindex();

        $this->sortVersions($aggregateLabel, $aggregateId);
    }

    public function removeAll(): void
    {
        $this->connection()->reset();
    }

    public function transaction(Closure $operation): void
    {
        $operation($this);
    }

    protected function sortVersions(string $aggregateLabel, string $aggregateId): void
    {
        $version = 1;

        $eventList = $this->connection()->all();

        foreach ($eventList as $index => $register) {
            if (
                $register['aggregateLabel'] === $aggregateLabel
                && $register['aggregateId'] === $aggregateId
            ) {
                $this->connection()->changeVersion($index, $version);
                $version++;
            }
        }
    }
}
