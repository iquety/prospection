<?php

declare(strict_types=1);

namespace Tests;

use ArrayObject;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Iquety\Prospection\Domain\Core\IdentityObject;
use PHPUnit\Framework\TestCase as FrameworkTestCase;
use ReflectionObject;
use Tests\Domain\Core\Support\DummyEntity;
use Tests\Domain\Core\Support\DummyValue;
use Tests\Domain\Stream\Support\DummyStreamEntity;

class TestCase extends FrameworkTestCase
{
    public function getPropertyValue(object $instance, string $name): mixed
    {
        $reflection = new ReflectionObject($instance);
        $property = $reflection->getProperty($name);
        $property->setAccessible(true);

        return $property->getValue($instance);
    }

    public function dummyDateTimeFactory(
        string $expression = "now",
        string $timezone = "UTC",
        string $signature = DateTimeImmutable::class
    ): DateTimeInterface
    {
        return new $signature($expression, new  DateTimeZone($timezone));
    }

    public function stateValues(): array
    {
        return [
            'aggregateId' => new IdentityObject('123456'),
            'one' => 'Ricardo',
            'two' => 30,
            'three' => 5.5,
            'four' => $this->dummyDateTimeFactory("now", "UTC"),
            'five' => new ArrayObject(),
            'six' => new DummyValue('test1'),
            'seven' => new DummyEntity(new IdentityObject('111'), 'test2'),
        ];
    }

    public function dummyStreamEntityFactory(
        string $expression = "now",
        string $timezone = "UTC"
    ): DummyStreamEntity {

        $values = $this->stateValues();
        $values['occurredOn'] = $this->dummyDateTimeFactory($expression, $timezone);

        return DummyStreamEntity::factory($values);
    }
}
