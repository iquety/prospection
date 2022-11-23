<?php

declare(strict_types=1);

namespace Tests\Domain\Stream;

use InvalidArgumentException;
use Iquety\Prospection\Domain\Stream\State;
use Tests\TestCase;

class StateConstructTest extends TestCase
{
    /** @test */
    public function withoutValues(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "The aggregation root must have a constructor containing the entity's state values"
        );

        new State([]);
    }

    /** @test */
    public function invalidStructure(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "The name of a value must be textual. " .
            "The value '123' provided is invalid"
        );

        new State([
            123
        ]);
    }

    /** @test */
    public function withoutAggregateId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "The aggregation root must have an entry for 'aggregateId'"
        );

        new State([
            'name'
        ]);
    }
}
