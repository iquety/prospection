<?php

declare(strict_types=1);

namespace Tests\Materialization;

use Iquety\Prospection\Materialization\MaterialField;
use OutOfRangeException;
use Tests\TestCase;

/** @SuppressWarnings(PHPMD.TooManyPublicMethods) */
class MaterialFieldTest extends TestCase
{
    /** @test */
    public function specific(): void
    {
        $field = new MaterialField('um');
        $field->fromAggregate('nome_completo');

        $this->assertTrue($field->hasValue());
        $this->assertEquals('um', $field->name());
        $this->assertEquals('nome_completo', $field->mapping()->stateField());
        $this->assertEquals('', $field->mapping()->stateChildField());
    }

    /** @test */
    public function compositeAndSpecific(): void
    {
        $field = new MaterialField('um');
        $field->fromAggregate('nome_completo', 'sobrenome');

        $this->assertTrue($field->hasValue());
        $this->assertEquals('um', $field->name());
        $this->assertEquals('nome_completo', $field->mapping()->stateField());
        $this->assertEquals('sobrenome', $field->mapping()->stateChildField());
    }

    /** @test */
    public function indeterminate(): void
    {
        $field = new MaterialField('um');

        $this->assertFalse($field->hasValue());
        $this->assertEquals('um', $field->name());
        $this->assertEquals('unknown', $field->mapping()->stateField());
        $this->assertEquals('', $field->mapping()->stateChildField());
    }

    /** @test */
    public function settings(): void
    {
        $field = new MaterialField('um');
        $field->config('qualquer', '2');
        $this->assertEquals('2', $field->getConfig('qualquer'));
    }

    /** @test */
    public function configNotExists(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage("Configuration parameter 'inexistente' does not exist");

        $field = new MaterialField('um');
        $this->assertEquals('', $field->getConfig('inexistente'));
    }
}
