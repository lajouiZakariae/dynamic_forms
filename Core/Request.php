<?php

namespace Core;

class Request
{

    static function uri(): string
    {
        return $_SERVER['REQUEST_URI'];
    }

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
        if (!isset($_GET[$name])) return false;

        if (is_string($_GET[$name])) {
            return strlen($_GET[$name]) > 0;
        }

        if (is_array($_GET[$name])) {
            return !empty($_GET[$name]);
        }
    }

    static function inputExists(string $name): bool
    {
        if (!isset($_POST[$name])) return false;

        if (is_string($_POST[$name])) {
            return strlen($_POST[$name]) > 0;
        }

        if (is_array($_POST[$name])) {
            return !empty($_POST[$name]);
        }
    }

    static function paramMissing(string $name): bool
    {
        return !self::paramExists($name);
    }

    static function inputMissing(string $name): bool
    {
        return !self::inputExists($name);
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

    /**
     * Value Checkers with Callbacks
     */

    /**
     * Execute $callback when $name equals $value
     * @param string $name Description
     * @param mixed $value Description
     * @param callback $callback Description
     * @return void
     **/
    static function whenParam(string $name, $value, callable $callback): void
    {
        self::isParam($name, $value) ? $callback() : null;
    }

    /**
     * Validators
     */
    static function isParamInt(string $name): bool
    {
        return filter_var(self::param($name), FILTER_VALIDATE_INT) !== false;
    }

    static function isInputInt(string $name): bool
    {
        return filter_var(self::input($name), FILTER_VALIDATE_INT) !== false;
    }

    static function paramInteger(string $name): ?int
    {
        return self::isParamInt($name) ? intval(self::param($name)) : null;
    }
}
