<?php

declare(strict_types=1);

namespace Tests\Stream\StreamEntity;

use Iquety\Prospection\EventStore\EventSnapshot;
use Tests\TestCase;

class SnapshotFactoryTest extends TestCase
{
    /** @test */
    public function toSnapshot(): void
    {
        $object = $this->streamEntityFactory();

        $this->assertInstanceOf(EventSnapshot::class, $object->toSnapshot());
    }

    /** @test */
    public function toSnapshotException(): void
    {
        $object = $this->streamEntityFactory();

        $this->assertInstanceOf(EventSnapshot::class, $object->toSnapshot());
    }
}
