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

    static function paramExists(string $name): bool
    {
        return isset($_GET[$name]);
    }

    static function inputExists(string $name): bool
    {
        return isset($_POST[$name]);
    }

    static function param(string $name): ?string
    {
        return self::paramExists($name) ? $_GET[$name] : null;
    }

    static function input(string $name): ?string
    {
        return self::inputExists($name) ? (!empty($_POST[$name]) ? $_POST[$name] : null) : null;
    }

    static function isParam(string $name, $value): bool
    {
        return self::param($name) == $value;
    }

    static function isInput(string $name, $value): bool
    {
        return self::input($name) == $value;
    }
}
