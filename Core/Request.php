<?php

namespace Core;

class Request
{

    static  function method(): string
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    static  function isPost(): string
    {
        return self::method() === "post";
    }

    static  function isGet(): string
    {
        return self::method() === "get";
    }

    /**
     * Existance Checkers
     */
    static function paramExists(string $name): bool
    {
        return isset($_GET[$name]) && !empty($_GET[$name]);
    }

    static function inputExists(string $name): bool
    {
        return isset($_POST[$name]) && !empty($_POST[$name]);
    }

    /**
     * Getters
     */
    static function param(string $name): string|array|null
    {
        return self::paramExists($name) ? $_GET[$name] : null;
    }

    static function input(string $name): string|array|null
    {
        return self::inputExists($name) ? $_POST[$name] : null;
    }

    /**
     * Value Checkers
     */
    static function isParam(string $name, $value): bool
    {
        return self::param($name) == $value;
    }

    static function isInput(string $name, $value): bool
    {
        return self::input($name) == $value;
    }
}
