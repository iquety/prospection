<?php

declare(strict_types=1);

namespace Tests\EventStore;

use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\EventStore\StreamId;
use Tests\TestCase;

class StreamIdTest extends TestCase
{
    /** @test */
    public function withNewVersion(): void
    {
        /** @var InvocationMocker */
        $aggregateId = $this->createMock(IdentityObject::class);
        $aggregateId->method('value')->willReturn('1234567');

        /** @var IdentityObject $aggregateId */
        $streamId = new StreamId($aggregateId, 2);

        $this->assertEquals(2, $streamId->version());

        $streamId2 = $streamId->withNewVersion(5);
        $this->assertEquals(5, $streamId2->version());

        // $this->assertNotEquals($streamId, $streamId2);
        // $this->assertTrue($streamId->aggregateId()->equalTo($streamId2->aggregateId()));
    }
}
