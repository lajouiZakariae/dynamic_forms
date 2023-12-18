<?php

function scriptParentDir(): string {
    return basename(dirname($_SERVER['SCRIPT_NAME']));
}

function css($name): void {
    echo CSS_PATH . (str_starts_with($name, '/') ? '' : '/') . $name;
}

function js($name): void {
    echo JS_PATH . (str_starts_with($name, '/') ? '' : '/') . $name;
}

function titleCase(string $str): string {
    return ucfirst(str_replace('_', ' ', $str));
}
