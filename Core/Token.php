<?php

namespace Core\support;

class Token
{
    public static function generate()
    {
        return md5(CSRF_TOKEN . uniqid(random_bytes(8), true));
    }

    public static function verify(string $token)
    {
        return Session::get("token") === $token;
    }
}
