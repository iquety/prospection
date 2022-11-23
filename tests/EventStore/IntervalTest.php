<?php

declare(strict_types=1);

namespace Tests\EventStore;

use Iquety\Prospection\EventStore\Interval;
use Tests\TestCase;

class IntervalTest extends TestCase
{
    /** @test */
    public function withoutOffset(): void
    {
        $interval = new Interval(33);

        $this->assertEquals(33, $interval->registers());
        $this->assertEquals(0, $interval->offset());
    }

    /** @test */
    public function withOffsetZero(): void
    {
        $interval = new Interval(33, 0);

        $this->assertEquals(33, $interval->registers());
        $this->assertEquals(0, $interval->offset());
    }

    /** @test */
    public function withOffset(): void
    {
        $interval = new Interval(33, 5);

        $this->assertEquals(33, $interval->registers());
        $this->assertEquals(5, $interval->offset());
    }
}
