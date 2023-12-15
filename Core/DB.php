<?php

namespace Core;

class DB
{
    private static string $host_name = HOSTNAME;
    private static string $dbname = DBNAME;
    private static string $password = PASSWORD;
    private static string $username = USERNAME;

    private static ?\PDO $pdo = null;

    private static function connect()
    {
        try {
            self::$pdo = new \PDO(
                "mysql:host=" . self::$host_name . ";dbname=" . self::$dbname . ";",
                self::$username,
                self::$password
            );
            self::$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    static function table(string $table): SQLQuery
    {
        if (!self::$pdo) self::connect();
        return new SQLQuery(self::$pdo, $table);
    }
}
