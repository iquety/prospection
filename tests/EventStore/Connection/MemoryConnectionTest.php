<?php

declare(strict_types=1);

namespace Tests\EventStore\Connection;

use Iquety\Prospection\EventStore\Memory\MemoryConnection;
use OutOfRangeException;
use Tests\TestCase;

class MemoryConnectionTest extends TestCase
{
    public function setUp(): void
    {
        MemoryConnection::instance()->reset();
    }

    /** @test */
    public function changeVersion(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage('Invalid event index');

        MemoryConnection::instance()->changeVersion(1, 33);
    }
}
