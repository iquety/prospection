<?php

declare(strict_types=1);

namespace Tests\Domain\Stream;

use DateTime;
use DateTimeImmutable;
use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\EventStore\EventSnapshot;
use Tests\Domain\Core\Support\DummyEntity;
use Tests\Domain\Core\Support\DummyValue;
use Tests\Domain\Stream\Support\DummyStreamEntity;
use Tests\TestCase;

class StreamEntityTest extends TestCase
{
    /** @test */
    public function constructionState(): void
    {
        $dateOne = new DateTimeImmutable();
        $dateTwo = new DateTime();
        
        $object = new DummyStreamEntity(
            new IdentityObject('123456'),
            'Ricardo',
            30,
            5.5,
            $dateOne,
            $dateTwo,
            new DummyValue('test1'),
            new DummyEntity(new IdentityObject('111'), 'test2'),
        );

        $this->assertEquals('Ricardo', $object->one());
        $this->assertEquals(30, $object->two());
        $this->assertEquals(5.5, $object->three());

        $object->changeState(new EventSnapshot([
            'aggregateId' => new IdentityObject('123456'),
            'one' => 'Pereira'
        ]));

        $this->assertEquals('Pereira', $object->one());
        $this->assertEquals(30, $object->two());
        $this->assertEquals(5.5, $object->three());
    }
}
