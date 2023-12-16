<?php

namespace Core\support;

class Session
{
    use  Singleton;

    protected function __construct()
    {
        session_set_cookie_params(["samesite" => "Strict"]);
        session_start();
    }

    public static function get(string $key): string
    {
        self::instance();
        return $_SESSION[$key] ?? false;
    }

    public static function set(string $key, string $value): void
    {
        self::instance();
        $_SESSION[$key] = $value;
    }

    public static function setIfNotExists(string $key, string $value): void
    {
        self::instance();
        if (self::missing($key)) {
            self::set($key, $value);
        }

    }

    public static function missing(string $key): bool
    {
        self::instance();
        return !isset($_SESSION[$key]);
    }

    public static function id(): string
    {
        self::instance();
        return session_id();
    }
    public static function destroy(): void
    {
        self::instance();
        session_reset();
        session_destroy();
    }
}
