<?php

declare(strict_types=1);

namespace Tests;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use PHPUnit\Framework\TestCase as FrameworkTestCase;
use ReflectionObject;

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
}
