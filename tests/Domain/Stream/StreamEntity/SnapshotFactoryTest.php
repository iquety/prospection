<?php

declare(strict_types=1);

namespace Tests\Domain\Stream\StreamEntity;

use Iquety\Prospection\EventStore\EventSnapshot;
use Tests\TestCase;

class SnapshotFactoryTest extends TestCase
{
    /** @test */
    public function toSnapshot(): void
    {
        $object = $this->dummyStreamEntityFactory();

        $this->assertInstanceOf(EventSnapshot::class, $object->toSnapshot());
    }

    /** @test */
    public function toSnapshotException(): void
    {
        $object = $this->dummyStreamEntityFactory();

        $this->assertInstanceOf(EventSnapshot::class, $object->toSnapshot());
    }
}
