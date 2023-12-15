<?php

namespace Core;

class Input
{

    public function __construct(
        protected Column $column,
        protected mixed $value
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
}
