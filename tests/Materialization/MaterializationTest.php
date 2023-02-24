<?php

declare(strict_types=1);

namespace Tests\Materialization;

use DateTimeZone;
use Iquety\Prospection\Materialization\AggregateField;
use Iquety\Prospection\Materialization\AggregateValue;
use Iquety\Prospection\Materialization\MaterialField;
use Iquety\Prospection\Materialization\Materialization;
use OutOfRangeException;
use Tests\TestCase;

/** @SuppressWarnings(PHPMD.TooManyPublicMethods) */
class MaterializationTest extends TestCase
{
    /** @test */
    public function table(): void
    {
        $materialization = new Materialization('tabela_principal');

        $this->assertEquals('tabela_principal', $materialization->table());
    }

    /** @test */
    public function getInvalidMaterialField(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage("Material field 'name' does not exist");

        $materialization = new Materialization('tabela_principal');

        $materialization->getMaterialField('name');
    }

    /** @test */
    public function hasMaterialField(): void
    {
        $materialization = new Materialization('tabela_principal');

        $this->assertFalse($materialization->hasMaterialField('name'));

        $materialization->string('name');

        $this->assertTrue($materialization->hasMaterialField('name'));
    }

    /** @test */
    public function getInvalidSortingField(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage("Sorting field 'name' does not exist");

        $materialization = new Materialization('tabela_principal');

        $materialization->getSortingField('name');
    }

    /** @test */
    public function hasSortingField(): void
    {
        $materialization = new Materialization('tabela_principal');

        $this->assertFalse($materialization->hasSortingField('name'));

        $materialization->sortByAscendancy('name');

        $this->assertTrue($materialization->hasSortingField('name'));
    }

    public function fieldsProvider(): array
    {
        $list = [];

        // binario
        $arguments = [ 'is_active' ];
        $config = [ MaterialField::TYPE => MaterialField::TYPE_BINARY ];
        $list['binary'] = [ 'binary', $arguments, $config ];

        // date
        $arguments = [ 'created_on' ];
        $config = [ MaterialField::TYPE => MaterialField::TYPE_DATE ];
        $list['date'] = [ 'date', $arguments, $config ];

        // datetime
        $arguments = [ 'created_on' ];
        $config = [
            MaterialField::TYPE => MaterialField::TYPE_DATETIME,
            MaterialField::TIMEZONE => 'UTC'
        ];
        $list['datetime'] = [ 'datetime', $arguments, $config ];

        $arguments = [ 'created_on', 'America_SaoPaulo' ];
        $config = [
            MaterialField::TYPE => MaterialField::TYPE_DATETIME,
            MaterialField::TIMEZONE => 'America_SaoPaulo'
        ];
        $list['datetime with timezone'] = [ 'datetime', $arguments, $config ];

        // decimal
        $arguments = [ 'price' ];
        $config = [
            MaterialField::TYPE => MaterialField::TYPE_DECIMAL,
            MaterialField::LENGTH => 10,
            MaterialField::DECIMAL_PLACES => 2
        ];
        $list['decimal'] = [ 'decimal', $arguments, $config ];

        $arguments = [ 'price', 8, 4 ];
        $config = [
            MaterialField::TYPE => MaterialField::TYPE_DECIMAL,
            MaterialField::LENGTH => 8,
            MaterialField::DECIMAL_PLACES => 4
        ];
        $list['decimal with custom places'] = [ 'decimal', $arguments, $config ];

        // hour
        $arguments = [ 'created_on' ];
        $config = [
            MaterialField::TYPE => MaterialField::TYPE_HOUR,
            MaterialField::TIMEZONE => 'UTC'
        ];
        $list['hour'] = [ 'hour', $arguments, $config ];

        $arguments = [ 'created_on', 'America_SaoPaulo' ];
        $config = [
            MaterialField::TYPE => MaterialField::TYPE_HOUR,
            MaterialField::TIMEZONE => 'America_SaoPaulo'
        ];
        $list['hour with timezone'] = [ 'hour', $arguments, $config ];
                
        // identity
        $arguments = [ 'id' ];
        $config = [
            MaterialField::TYPE => MaterialField::TYPE_IDENTITY,
            MaterialField::LENGTH => 50
        ];
        $list['identity'] = [ 'identity', $arguments, $config ];

        $arguments = [ 'id', 32 ];
        $config = [
            MaterialField::TYPE => MaterialField::TYPE_IDENTITY,
            MaterialField::LENGTH => 32
        ];
        $list['identity with custom length'] = [ 'identity', $arguments, $config ];

        // integer
        $arguments = [ 'amount' ];
        $config = [
            MaterialField::TYPE => MaterialField::TYPE_INTEGER,
            MaterialField::LENGTH => 10
        ];
        $list['integer'] = [ 'integer', $arguments, $config ];

        $arguments = [ 'amount', 4 ];
        $config = [
            MaterialField::TYPE => MaterialField::TYPE_INTEGER,
            MaterialField::LENGTH => 4
        ];
        $list['integer with custom length'] = [ 'integer', $arguments, $config ];

        // string
        $arguments = [ 'name' ];
        $config = [
            MaterialField::TYPE => MaterialField::TYPE_STRING,
            MaterialField::LENGTH => 50
        ];
        $list['string'] = [ 'string', $arguments, $config ];

        $arguments = [ 'name', 15 ];
        $config = [
            MaterialField::TYPE => MaterialField::TYPE_STRING,
            MaterialField::LENGTH => 15
        ];
        $list['string with custom length'] = [ 'string', $arguments, $config ];

        // text
        $arguments = [ 'description' ];
        $config = [ MaterialField::TYPE => MaterialField::TYPE_TEXT ];
        $list['text'] = [ 'text', $arguments, $config ];

        return $list;
    }

    /**
     * @test
     * @dataProvider fieldsProvider
     */
    public function materialField(string $method, array $arguments, array $configList): void
    {
        $materialization = new Materialization('table_name');
        $materialization->$method(...$arguments);

        $field = $materialization->getMaterialField($arguments[0]);

        $this->assertInstanceOf(MaterialField::class, $field);
        $this->assertEquals($arguments[0], $field->name());

        foreach ($configList as $param => $value) {
            $this->assertEquals($value, $field->getConfig($param));
        }

        // sem estado mapeado
        $this->assertInstanceOf(AggregateField::class, $field->mapping());
        $this->assertEquals('unknown', $field->mapping()->stateField());
        $this->assertEquals('', $field->mapping()->stateChildField());

        // mapeado para estado primitivo
        $field->fromAggregate('name');

        $this->assertEquals('name', $field->mapping()->stateField());
        $this->assertEquals('', $field->mapping()->stateChildField());

        // mapeado para estado com subvalor
        $field->fromAggregate('name', 'lastname');

        $this->assertEquals('name', $field->mapping()->stateField());
        $this->assertEquals('lastname', $field->mapping()->stateChildField());
    }

    /** @test */
    public function identityFieldControls(): void
    {
        $materialization = new Materialization('table_name');

        $this->assertFalse($materialization->hasMaterialField('created_on'));
        $this->assertFalse($materialization->hasMaterialField('updated_on'));

        $materialization->identity('uuid', 32);

        $this->assertTrue($materialization->hasMaterialField('created_on'));
        $this->assertTrue($materialization->hasMaterialField('updated_on'));

        $createdOn = $materialization->getMaterialField('created_on');
        $this->assertEquals(
            MaterialField::TYPE_STATE_CONTROL,
            $createdOn->getConfig(MaterialField::TYPE)
        );

        $updatedOn = $materialization->getMaterialField('updated_on');
        $this->assertEquals(
            MaterialField::TYPE_STATE_CONTROL,
            $updatedOn->getConfig(MaterialField::TYPE)
        );
    }

    public function sortingProvider(): array
    {
        $list = [];

        // sortByAscendancy
        $arguments = [ 'name' ];
        $config = [ 
            MaterialField::TYPE => MaterialField::TYPE_SORTING,
            MaterialField::SORTING_BY => MaterialField::SORTING_ASCENDANCY
        ];
        $list['sortByAscendancy'] = [ 'sortByAscendancy', $arguments, $config ];

        // sortByDescent
        $arguments = [ 'name' ];
        $config = [ 
            MaterialField::TYPE => MaterialField::TYPE_SORTING,
            MaterialField::SORTING_BY => MaterialField::SORTING_DESCENT
        ];
        $list['sortByDescent'] = [ 'sortByDescent', $arguments, $config ];

        return $list;
    }

    /**
     * @test
     * @dataProvider sortingProvider
     */
    public function sortingField(string $method, array $arguments, array $configList): void
    {
        $materialization = new Materialization('table_name');
        $materialization->$method(...$arguments);

        $field = $materialization->getSortingField($arguments[0]);

        $this->assertInstanceOf(MaterialField::class, $field);
        $this->assertEquals($arguments[0], $field->name());

        foreach ($configList as $param => $value) {
            $this->assertEquals($value, $field->getConfig($param));
        }

        // sem estado mapeado
        $this->assertInstanceOf(AggregateField::class, $field->mapping());
        $this->assertEquals('unknown', $field->mapping()->stateField());
        $this->assertEquals('', $field->mapping()->stateChildField());

        // mapeado para estado primitivo
        $field->fromAggregate('name');

        $this->assertEquals('name', $field->mapping()->stateField());
        $this->assertEquals('', $field->mapping()->stateChildField());

        // mapeado para estado com subvalor
        $field->fromAggregate('name', 'lastname');

        $this->assertEquals('name', $field->mapping()->stateField());
        $this->assertEquals('lastname', $field->mapping()->stateChildField());
    }
}
