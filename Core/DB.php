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

    // private function select(int $id = null)
    // {
    //     $query = "SELECT * FROM {$this->table}"
    //         . ($id ? " WHERE id=:id ;" : ";");

    //     $etudiants_stm = $this->pdo->prepare($query);

    //     if ($id) {
    //         $etudiants_stm->execute([":id" => $id]);
    //         return $etudiants_stm->fetch();
    //     } else {
    //         $etudiants_stm->execute();
    //         return $etudiants_stm->fetchAll();
    //     }
    // }

    // function all(): array
    // {
    //     $query = "SELECT * FROM {$this->table};";

    //     $etudiants_stm = $this->pdo->prepare($query);

    //     $etudiants_stm->execute();
    //     return $etudiants_stm->fetchAll();
    // }

    // function find(int $id)
    // {
    //     $query = "SELECT * FROM {$this->table} WHERE id=:id;";

    //     $etudiants_stm = $this->pdo->prepare($query);

    //     $etudiants_stm->execute([":id" => $id]);
    //     return $etudiants_stm->fetch();
    // }

    // function destroy(int $id): bool
    // {
    //     $query = "DELETE FROM {$this->table} WHERE id=:id;";

    //     $delete_stm = $this->pdo->prepare($query);

    //     $delete_stm->execute([
    //         ":id" => $id,
    //     ]);

    //     return $delete_stm->rowCount() > 0;
    // }

    // private function bindableParams(array $data)
    // {
    //     $params = [];

    //     foreach ($data as $k => $v) $params[str_starts_with($k, ':') ? "$k" : ":$k"] = $v;

    //     return $params;
    // }

    // function save(array $data): bool
    // {
    //     if (empty($data)) return false;

    //     $keys = array_keys($data);

    //     $query = "INSERT INTO {$this->table}"
    //         . "(" . implode(",", $keys) . ")"
    //         . " VALUES(" . implode(',', array_map(fn ($p) => ":$p", $keys)) . ");";

    //     $insert_stm = $this->pdo->prepare($query);

    //     $insert_stm->execute($this->bindableParams($data));

    //     return $insert_stm->rowCount() > 0;
    // }

    // function update(int $id, array $data)
    // {
    //     if (empty($data)) return false;

    //     $keys = array_keys($data);

    //     $query = "UPDATE {$this->table} SET "
    //         . implode(", ", array_map(fn ($k) => "$k=:$k", $keys))
    //         . " WHERE id=:id";

    //     $update_stm = $this->pdo->prepare($query);

    //     $data[":id"] = $id;
    //     $update_stm->execute($this->bindableParams($data));

    //     return $update_stm->rowCount() > 0;
    // }
}
