<?php

declare(strict_types=1);

namespace Tests;

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
}
