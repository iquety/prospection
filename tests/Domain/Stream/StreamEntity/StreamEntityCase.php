<?php

declare(strict_types=1);

namespace Tests\Domain\Stream\StreamEntity;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Iquety\Prospection\Domain\Core\IdentityObject;
use Iquety\Prospection\EventStore\EventSnapshot;
use Tests\Domain\Core\Support\DummyEntity;
use Tests\Domain\Core\Support\DummyValue;
use Tests\Domain\Stream\Support\DummyStreamEntity;
use Tests\TestCase;

class StreamEntityCase extends TestCase
{
    public function dummyDateTimeFactory(
        string $expression = "now",
        string $timezone = "UTC",
        string $signature = DateTimeImmutable::class
    ): DateTimeInterface
    {
        return new $signature($expression, new  DateTimeZone($timezone));
    }

    public function dummyStreamEntityFactory(
        string $expression = "now",
        string $timezone = "UTC"
    ): DummyStreamEntity {

        $dateOne = $this->dummyDateTimeFactory($expression, $timezone);
        $dateTwo = $this->dummyDateTimeFactory($expression, $timezone, DateTime::class);

        return DummyStreamEntity::factory([
            'aggregateId' => new IdentityObject('123456'),
            'one' => 'Ricardo',
            'two' => 30,
            'three' => 5.5,
            'four' => $dateOne,
            'five' => $dateTwo,
            'six' => new DummyValue('test1'),
            'seven' => new DummyEntity(new IdentityObject('111'), 'test2'),
            'occurredOn' => $dateOne // campo especial para setagem da ocorrência do evento
        ]);
    }
}
