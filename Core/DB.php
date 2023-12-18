<?php

namespace Core;

class DB {
    private static ?\PDO $pdo = null;

    private static function connect() {
        try {
            self::$pdo = new \PDO('mysql:hostname=localhost;dbname=db_ecole;', 'root', '');
            self::$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    static function sql($q, ?array $args = null) {
        if (is_null(self::$pdo)) self::connect();

        $q = trim($q);

        $operation = substr($q, 0, strpos($q, ' '));

        $stm = self::$pdo->prepare($q);

        $stm->execute($args);

        if ($operation === 'SELECT') {
            return $stm->fetchAll();
        } else {
            return $stm->rowCount();
        }
    }
}
