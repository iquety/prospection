<?php

declare(strict_types=1);

namespace Iquety\Prospection\Materialization;

use OutOfRangeException;

/** @SuppressWarnings(PHPMD.TooManyPublicMethods) */
class Materialization
{
    /** @var array<string,MaterialField> */
    protected array $materialFieldList = [];

    /** @var array<string,MaterialField> */
    protected array $sortingFieldList = [];

    public function __construct(private string $tableName)
    {
    }

    public function getMaterialField(string $fieldName): MaterialField
    {
        return $this->materialFieldList[$fieldName]
            ?? throw new OutOfRangeException("Material field '$fieldName' does not exist");
    }

    public function getSortingField(string $fieldName): MaterialField
    {
        return $this->sortingFieldList[$fieldName]
            ?? throw new OutOfRangeException("Sorting field '$fieldName' does not exist");
    }

    public function hasMaterialField(string $fieldName): bool
    {
        return isset($this->materialFieldList[$fieldName]);
    }

    public function hasSortingField(string $fieldName): bool
    {
        return isset($this->sortingFieldList[$fieldName]);
    }

    public function binary(string $fieldName): MaterialField
    {
        $field = $this->fieldFactory($fieldName);
        $field->config(MaterialField::TYPE, MaterialField::TYPE_BINARY);

        return $field;
    }

    public function string(string $fieldName, int $length = 50): MaterialField
    {
        $field = $this->fieldFactory($fieldName);
        $field->config(MaterialField::TYPE, MaterialField::TYPE_STRING);
        $field->config(MaterialField::LENGTH, (string)$length);

        return $field;
    }

    public function date(string $fieldName): MaterialField
    {
        $field = $this->fieldFactory($fieldName);
        $field->config(MaterialField::TYPE, MaterialField::TYPE_DATE);

        return $field;
    }

    public function datetime(string $fieldName, string $timezone = "UTC"): MaterialField
    {
        $field = $this->fieldFactory($fieldName);
        $field->config(MaterialField::TYPE, MaterialField::TYPE_DATETIME);
        $field->config(MaterialField::TIMEZONE, $timezone);

        return $field;
    }

    public function decimal(string $fieldName, int $length = 10, int $decimalPlaces = 2): MaterialField
    {
        $field = $this->fieldFactory($fieldName);
        $field->config(MaterialField::TYPE, MaterialField::TYPE_DECIMAL);
        $field->config(MaterialField::LENGTH, (string)$length);
        $field->config(MaterialField::DECIMAL_PLACES, (string)$decimalPlaces);

        return $field;
    }

    public function hour(string $fieldName, string $timezone = "UTC"): MaterialField
    {
        $field = $this->fieldFactory($fieldName);
        $field->config(MaterialField::TYPE, MaterialField::TYPE_HOUR);
        $field->config(MaterialField::TIMEZONE, $timezone);

        return $field;
    }

    public function identity(string $fieldName, int $length = 50): MaterialField
    {
        $field = $this->fieldFactory($fieldName);
        $field->config(MaterialField::TYPE, MaterialField::TYPE_IDENTITY);
        $field->config(MaterialField::LENGTH, (string)$length);

        $fieldCriacao = $this->fieldFactory('created_on');
        $fieldCriacao->config(MaterialField::TYPE, MaterialField::TYPE_STATE_CONTROL);
        $fieldCriacao->fromAggregate('createdOn');

        $fieldAlteracao = $this->fieldFactory('updated_on');
        $fieldAlteracao->config(MaterialField::TYPE, MaterialField::TYPE_STATE_CONTROL);
        $fieldAlteracao->fromAggregate('updatedOn');

        return $field;
    }

    public function integer(string $fieldName, int $length = 10): MaterialField
    {
        $field = $this->fieldFactory($fieldName);
        $field->config(MaterialField::TYPE, MaterialField::TYPE_INTEGER);
        $field->config(MaterialField::LENGTH, (string)$length);

        return $field;
    }

    public function sortByAscendancy(string $fieldName): MaterialField
    {
        $field = $this->sortingFactory($fieldName);
        $field->config(MaterialField::TYPE, MaterialField::TYPE_SORTING);
        $field->config(MaterialField::SORTING_BY, MaterialField::SORTING_ASCENDANCY);

        return $field;
    }

    public function sortByDescent(string $fieldName): MaterialField
    {
        $field = $this->sortingFactory($fieldName);
        $field->config(MaterialField::TYPE, MaterialField::TYPE_SORTING);
        $field->config(MaterialField::SORTING_BY, MaterialField::SORTING_DESCENT);

        return $field;
    }

    /** @return array<string,MaterialField> */
    public function materialFieldList(): array
    {
        return $this->materialFieldList;
    }

    /** @return array<string,MaterialField> */
    public function sortingFieldList(): array
    {
        return $this->sortingFieldList;
    }

    public function table(): string
    {
        return $this->tableName;
    }

    public function text(string $fieldName): MaterialField
    {
        $field = $this->fieldFactory($fieldName);
        $field->config(MaterialField::TYPE, MaterialField::TYPE_TEXT);

        return $field;
    }

    // Support

    private function fieldFactory(string $fieldName): MaterialField
    {
        $this->materialFieldList[$fieldName] = new MaterialField($fieldName);

        return $this->materialFieldList[$fieldName];
    }

    private function sortingFactory(string $fieldName): MaterialField
    {
        $this->sortingFieldList[$fieldName] = new MaterialField($fieldName);

        return $this->sortingFieldList[$fieldName];
    }
}
