<?php

use Core\Column;
use Core\DB;
use Core\Paginator;

/**
 * @param Column[] $columns
 */
function stringfyColumns($columns) {
    $str = '';

    foreach ($columns as $index => $column) {
        if ($index === count($columns) - 1) $str .= '`' . $column . '`';
        else $str .= '`' . $column . '`' .  ',';
    }

    return $str;
}

function tableMissing(string $table): bool {
    $count = DB::sql(
        "SELECT COUNT(TABLE_NAME) AS table_count 
        FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = :db_name
        AND TABLE_NAME = :table_name;",
        [':db_name' => 'db_ecole', 'table_name' => $table]
    )[0]->table_count;
    return ($count === 0);
}

/**
 * @param string $table 
 * @return Column[]
 **/
function getColumns(string $table, ?array $only = null, ?array $excludes = null) {
    $excludes_string = $excludes
        ? implode('', array_map(fn () => "AND COLUMN_NAME != ? ", $excludes))
        : null;

    $onlys_string = $only
        ? implode('', array_map(fn () => "AND COLUMN_NAME = ? ", $only))
        : null;

    $params = ['db_ecole', $table];

    if ($only) {
        foreach ($only as $param)
            $params[] = $param;
    } elseif ($excludes) {
        foreach ($excludes as $param)
            $params[] = $param;
    }

    $sql = "SELECT COLUMN_NAME,DATA_TYPE,COLUMN_TYPE,COLUMN_KEY,CHARACTER_MAXIMUM_LENGTH,IS_NULLABLE 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_SCHEMA = ?
            AND TABLE_NAME = ?
            " . ($only ? $onlys_string : ($excludes ? $excludes_string : '')) . "
            ORDER BY ORDINAL_POSITION ASC;";

    $result = DB::sql($sql, $params);

    return array_map(fn ($col) => new Column($col), $result);
}

function getPrimaryKey(string $table) {
    $sql = "SELECT COLUMN_NAME 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = ?
    AND TABLE_NAME = ?
    AND COLUMN_KEY = ?
    ORDER BY ORDINAL_POSITION ASC;";

    $result = DB::sql($sql, ['db_ecole', $table, 'PRI']);
    return $result[0]->COLUMN_NAME;
}

function select(string $table, ?array $columns = null, ?array $options = null) {
    $sql = "SELECT " . ($columns ? stringfyColumns($columns) : '*') . " FROM `{$table}`";

    if ($options) {
        $sql .= $options["limit"] ? (" LIMIT " . $options['limit']) : "";
        $sql .= $options["offset"] ? (" OFFSET " . $options['offset']) : "";
    }

    $sql .= ";";

    return DB::sql($sql);
}

function find(string $table, mixed $id): object|false {
    $result = DB::sql("SELECT * FROM `$table` WHERE " . getPrimaryKey($table) . "= ? LIMIT 1;", [$id]);
    if (empty($result)) return false;
    return $result[0];
}

/**
 * @param string $table
 * @param Input[] $data
 * @param mixed $id
 */
function update(string $table,  $data, mixed $id) {
    $sql = "UPDATE `{$table}` SET ";

    $values = [];
    foreach ($data as $key => $value) {
        $sql .= "`$key`=? , ";
        $values[] = $value;
    };

    $sql = substr($sql, 0, -2);

    $sql .=  " WHERE " . getPrimaryKey($table) . "=?;";

    $values[] = $id;

    return DB::sql($sql, $values);
}

function insert(string $table, array $data) {

    $cols = [];
    $values = [];

    foreach ($data as $col => $val) {
        $cols[] = $col;
        $values[] = $val;
    }

    $sql = "INSERT INTO `{$table}` (" . stringfyColumns($cols) . ") VALUES (" . substr(str_repeat('?,', count($data)), 0, -1) . ");";

    return DB::sql($sql, $values);
}

function dataCount(string $table) {
    $sql = "SELECT COUNT(*) AS table_count FROM `$table`;";

    $result = DB::sql($sql);

    return $result[0]->table_count;
}

function paginate(string $table, int $per_page = 10) {
    $page = paramInteger('page', 1);

    $count = dataCount($table);

    if ($count === 0) return new Paginator([]);

    $page_count = (int) ceil($count / $per_page);

    // Out of scope
    if ($page <= 0 || $page > $page_count) return redirect('index.php');

    // Pagination Not needed
    if ($page_count <= 2) return new Paginator(select($table));

    $offset = ($page - 1)  * $per_page;

    return new Paginator(select($table, options: ['limit' => $per_page, 'offset' => $offset]), last: $page_count);
}
