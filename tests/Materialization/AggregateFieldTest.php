<?php

declare(strict_types=1);

namespace Tests\Materialization;

use DateTimeImmutable;
use InvalidArgumentException;
use Iquety\Prospection\EventStore\Descriptor;
use Iquety\Prospection\Materialization\Field;
use Iquety\Prospection\Materialization\MaterialField;
use Tests\Stream\Support\DummyStreamEntity;
use Tests\TestCase;

/** @SuppressWarnings(PHPMD.TooManyPublicMethods) */
class AggregateFieldTest extends TestCase
{
    /** @test */
    public function simpleValue(): void
    {
        $field = new MaterialField('um');
        $field->fromAggregate('nomeCompleto');

        $this->assertEquals('nomeCompleto', $field->mapping()->stateField());
        $this->assertEquals('', $field->mapping()->stateChildField());
    }

    /** @test */
    public function compositeValue(): void
    {
        $field = new MaterialField('um');
        $field->fromAggregate('nomeCompleto', 'sobrenome');

        $this->assertEquals('nomeCompleto', $field->mapping()->stateField());
        $this->assertEquals('sobrenome', $field->mapping()->stateChildField());
    }

    /** @test */
    public function extractState(): void
    {
        $entity = $this->dummyStreamEntityFactory();
        $eventSnapshot = $entity->toSnapshot();

        $descriptor = new Descriptor(
            DummyStreamEntity::class,
            $eventSnapshot,
            new DateTimeImmutable("2022-10-10 10:10:10.703961"),
            new DateTimeImmutable("2022-10-10 11:10:10.703961")
        );

        $field = new MaterialField('um');
        $field->fromAggregate('seven', 'myValue');

        $this->assertEquals('test2', $field->mapping()->extractState($descriptor));
    }

    /** @test */
    public function extractStateValueNotExists(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "The specified descriptor does not contain the value 'nomeCompletissimo'"
        );

        $entity = $this->dummyStreamEntityFactory();
        $eventSnapshot = $entity->toSnapshot();

        $descriptor = new Descriptor(
            DummyStreamEntity::class,
            $eventSnapshot,
            new DateTimeImmutable("2022-10-10 10:10:10.703961"),
            new DateTimeImmutable("2022-10-10 11:10:10.703961")
        );

        $field = new MaterialField('um');
        $field->fromAggregate('nomeCompletissimo', 'sobrenome');

        $field->mapping()->extractState($descriptor);
    }

    /** @test */
    public function extractStateElementNotExists(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            "The specified descriptor does not contain the child value 'sobrenominho' for the value 'seven'"
        );

        $entity = $this->dummyStreamEntityFactory();
        $eventSnapshot = $entity->toSnapshot();

        $descriptor = new Descriptor(
            DummyStreamEntity::class,
            $eventSnapshot,
            new DateTimeImmutable("2022-10-10 10:10:10.703961"),
            new DateTimeImmutable("2022-10-10 11:10:10.703961")
        );

        $field = new MaterialField('um');
        $field->fromAggregate('seven', 'sobrenominho');

        $field->mapping()->extractState($descriptor);
    }
}
