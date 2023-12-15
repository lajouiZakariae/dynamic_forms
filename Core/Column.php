<?php

namespace Core;

class Column
{
    private string $name;
    private string $type;
    private ?array $allowed_values;
    private ?int $max_length;
    private bool $nullable;
    private bool $primary;
    private bool $signed;
    private array $numeric_types = ['tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'bit', 'float', 'double', 'decimal'];
    private array $big_text_types = ['tinytext',  'mediumtext', 'text', 'longtext'];

    public function __construct(object $_column)
    {
        $this->name = $_column->COLUMN_NAME;
        $this->type = $_column->DATA_TYPE;
        $this->max_length = $_column->CHARACTER_MAXIMUM_LENGTH;
        $this->nullable = $_column->IS_NULLABLE === 'YES';
        $this->primary = $_column->COLUMN_KEY === "PRI";
        $this->allowed_values = null;
        $this->signed = false;

        if (in_array($_column->DATA_TYPE, ['enum', 'set'])) {
            $this->allowed_values = $this->extractAllowedValues($_column->COLUMN_TYPE);
        }

        if ($this->isNumeric($_column->DATA_TYPE)) {
            $this->signed = str_contains($_column->COLUMN_TYPE, 'unsigned');
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAllowedValues(): array
    {
        return $this->allowed_values;
    }

    public function isText(): string
    {
        return in_array($this->type, $this->big_text_types);
    }

    function isNumeric(): bool
    {
        return in_array($this->type, $this->numeric_types);
    }

    function isEnum(): bool
    {
        return $this->type === 'enum';
    }

    function isSet(): bool
    {
        return $this->type === 'set';
    }

    function isPrimary(): bool
    {
        return $this->primary;
    }

    function normalizeSetValues(string $value): array
    {
        if (empty($value)) return [];
        return explode(',', $value);
    }

    private function extractAllowedValues($column_type): array
    {
        $pos_of_first = strpos($column_type, '(');
        $allowed_values_as_string = substr($column_type, $pos_of_first + 1, -1);

        return array_map(
            fn (string $val) => substr($val, 1, strlen($val) - 2),
            explode(',', $allowed_values_as_string)
        );
    }
}
