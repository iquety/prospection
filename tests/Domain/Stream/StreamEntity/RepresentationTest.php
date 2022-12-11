<?php

declare(strict_types=1);

namespace Tests\Domain\Stream\StreamEntity;

use Tests\Domain\Stream\Support\DummyStreamEntity;
use Tests\TestCase;

class RepresentationTest extends TestCase
{
    /** @test */
    public function toArrayData(): void
    {
        $stateValues = $this->stateValues();

        $factoryValues = $stateValues;
        $factoryValues['occurredOn'] = $this->dummyDateTimeFactory("now", "UTC");

        $object = DummyStreamEntity::factory($factoryValues);
       
        $compareValues = $stateValues;
        $compareValues['createdOn'] = $factoryValues['occurredOn'];
        $compareValues['updatedOn'] = $factoryValues['occurredOn'];
        $compareValues['occurredOn'] = $factoryValues['occurredOn'];

        $this->assertEquals($compareValues, $object->toArray());
    }

    /** @test */
    public function stringRepresentation(): void
    {
        $values = $this->stateValues();
        $values['four'] = $this->dummyDateTimeFactory("2022-10-10 10:10:10", "UTC");
        $values['occurredOn'] = $this->dummyDateTimeFactory("2022-10-10 10:10:10", "UTC");

        $object = DummyStreamEntity::factory($values);

        $this->assertEquals("DummyStreamEntity [\n" . 
            "    aggregateId = IdentityObject [123456]\n" .
            "    one = Ricardo\n" .
            "    two = 30\n" .
            "    three = 5.5\n" .
            "    four = DateTimeImmutable UTC [2022-10-10 10:10:10.000000]\n" .
            "    five = ArrayObject()\n" .
            "    six = DummyValue [test1]\n" .
            "    seven = DummyEntity [...]\n" .
            "    createdOn = DateTimeImmutable UTC [2022-10-10 10:10:10.000000]\n" .
            "    updatedOn = DateTimeImmutable UTC [2022-10-10 10:10:10.000000]\n" .
            "    occurredOn = DateTimeImmutable UTC [2022-10-10 10:10:10.000000]\n" .
        "]", (string)$object);
    }
}
