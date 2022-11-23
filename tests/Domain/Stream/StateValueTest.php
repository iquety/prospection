<?php

declare(strict_types=1);

namespace Tests\Domain\Stream;

use DomainException;
use Iquety\Prospection\Domain\Stream\State;
use OutOfRangeException;
use Tests\TestCase;

class StateValueTest extends TestCase
{
    /** @test */
    public function invalidValue(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage(
            "The queried value 'document' does not belong to the current state"
        );

        $state = new State([
            'aggregateId',
            'name',
            'email'
        ]);

        $state->value('document');
    }

    /** @test */
    public function unconsolidatedValue(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage(
            "The queried value 'name' is not filled yet"
        );

        $state = new State([
            'aggregateId',
            'name',
            'email'
        ]);

        $state->value('name');
    }
}
