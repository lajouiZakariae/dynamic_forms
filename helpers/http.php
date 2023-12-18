<?php

function requestMethod(): string {
    return strtolower($_SERVER['REQUEST_METHOD']);
}

function isPost(): string {
    return requestMethod() === "post";
}

function isGet(): string {
    return requestMethod() === "get";
}

function paramExists(string $name): bool {
    if (!isset($_GET[$name])) return false;

    if (is_string($_GET[$name])) {
        return strlen($_GET[$name]) > 0;
    }

    if (is_array($_GET[$name])) {
        return !empty($_GET[$name]);
    }
}

function inputExists(string $name): bool {
    if (!isset($_POST[$name])) return false;

    if (is_string($_POST[$name])) {
        return strlen($_POST[$name]) > 0;
    }

    if (is_array($_POST[$name])) {
        return !empty($_POST[$name]);
    }
}

function paramMissing(string $name): bool {
    return !paramExists($name);
}

function inputMissing(string $name): bool {
    return !inputExists($name);
}

/**
 * value getters
 */
function param(string $name): mixed {
    return paramExists($name) ? $_GET[$name] : null;
}

function input(string $name): mixed {
    return inputExists($name) ? $_POST[$name] : null;
}

/**
 * Value Checkers
 */
function isParam(string $name, $value): bool {
    return param($name) == $value;
}

function isInput(string $name, $value): bool {
    return input($name) == $value;
}

function whenParam(string $name, $value, callable $callback): void {
    isParam($name, $value) ? $callback() : null;
}

/**
 * 
 */
function paramInteger(string $name, mixed $default = null) {
    if ($default && !paramExists($name)) return $default;

    return (paramExists($name) && (filter_var(param($name), FILTER_VALIDATE_INT)) !== false)
        ? (int)param($name) : null;
};
