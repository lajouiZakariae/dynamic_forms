<?php

namespace Core;

class Column {
    private string $name;
    private string $type;
    private ?array $allowed_values;
    private ?int $max_length;
    private bool $nullable;
    private bool $primary;
    private bool $signed;
    private array $numeric_types = ['tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'bit', 'float', 'double', 'decimal'];
    private array $big_text_types = ['tinytext',  'mediumtext', 'text', 'longtext'];

    public function __construct(object $_column) {
        $this->name = $_column->COLUMN_NAME;
        $this->type = $_column->DATA_TYPE;
        $this->max_length = $_column->CHARACTER_MAXIMUM_LENGTH;
        $this->nullable = $_column->IS_NULLABLE === 'YES';
        $this->primary = $_column->COLUMN_KEY === "PRI";
        $this->allowed_values = null;
        $this->signed = false;

        if (in_array($_column->DATA_TYPE, ['enum', 'set'])) {
            $this->allowed_values = $this->extractPossibleValues($_column->COLUMN_TYPE);
        }

        if ($this->isNumeric($_column->DATA_TYPE)) {
            $this->signed = str_contains($_column->COLUMN_TYPE, 'unsigned');
        }
    }

    public function getName(): string {
        return $this->name;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getAllowedValues(): array {
        return $this->allowed_values;
    }

    function isNumeric(): bool {
        return in_array($this->type, $this->numeric_types);
    }

    function isPrimary(): bool {
        return $this->primary;
    }

    private function extractPossibleValues($column_type): array {
        $pos_of_first = strpos($column_type, '(');
        $allowed_values_as_string = substr($column_type, $pos_of_first + 1, -1);

        return array_map(
            fn (string $val) => substr($val, 1, strlen($val) - 2),
            explode(',', $allowed_values_as_string)
        );
    }

    /**
     * Returns the input html type
     * Based on column type
     * @param string $columnType
     * @return string
     */
    function getInputType(): string {
        $type = 'text';

        if ($this->getType() === 'varchar' && $this->getName() === 'email') $type = 'email';

        if ($this->getType() === 'varchar' && $this->getName() === 'password') $type = 'password';

        if ($this->getType() === 'enum') $type = 'enum';

        if ($this->getType() === 'set') $type = 'set';

        if ($this->getType() === 'date') $type = 'date';

        if ($this->getType() === 'time') $type = 'time';

        if ($this->getType() === 'datetime') $type = 'datetime-local';

        if (in_array($this->getType(), $this->big_text_types)) $type = 'textarea';

        return $type;
    }

    function isText() {
        return $this->getInputType() === 'text';
    }

    function isTextArea() {
        return $this->getInputType() === 'textarea';
    }

    function isDate() {
        return $this->getInputType() === 'date';
    }

    function isDateTime() {
        return $this->getInputType() === 'datetime-local';
    }

    function isTime() {
        return $this->getInputType() === 'time';
    }

    function isEnum() {
        return $this->getInputType() === 'enum';
    }

    function isSet() {
        return $this->getInputType() === 'set';
    }

    function normalizeSetValues(string $value): array {
        if (empty($value)) return [];
        return explode(',', $value);
    }

    function stringifySetValues(array $value): string {
        if (empty($value)) return "";
        return implode(',', $value);
    }
}
