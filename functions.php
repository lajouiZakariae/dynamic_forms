<?php

function dump($p): void
{
    echo '<pre>';
    var_dump($p);
    echo '</pre>';
}

function titleCase(string $str): string
{
    return ucfirst(str_replace('_', ' ', $str));
}


function css($name): void
{
    echo CSS_PATH . (str_starts_with($name, '/') ? '' : '/') . $name;
}

function js($name): void
{
    echo JS_PATH . (str_starts_with($name, '/') ? '' : '/') . $name;
}

function scriptParentDir(): string
{
    return basename(dirname($_SERVER['SCRIPT_NAME']));
}

function isCurrentPage(string $value): bool
{
    return scriptParentDir($_SERVER['SCRIPT_NAME']) === $value;
}
