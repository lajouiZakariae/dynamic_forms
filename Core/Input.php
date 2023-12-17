<?php

namespace Core;

class Input
{

    public function __construct(
        private Column $column,
        private mixed $value
    ) {
    }

    function getColumn(): Column
    {
        return $this->column;
    }

    function getValue(): mixed
    {
        return $this->value;
    }

    function isValueExists(string|array|null $val): bool
    {
        if ($this->getValue() === null) return false;

        $set_values = is_array($this->getValue())
            ? $this->getValue() :
            $this->getColumn()->normalizeSetValues($this->getValue());

        return in_array($val, $set_values);
    }
}
