<?php

namespace Core;

trait Singleton
{
    protected static $inst = null;

    protected function __construct()
    {
    }

    public static function instance(): self
    {
        if (!self::$inst) {
            self::$inst = new static();
        }

        return self::$inst;
    }
}
