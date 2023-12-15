<?php

namespace Core;

class SQLQuery
{
    /** @var Column[] $table_columns  */
    protected $table_columns = null;

    private string $sql = '';

    private ?array $conditions = null;

    private ?array $sort = null;

    private ?int $lim = null;

    function __construct(
        private \PDO $pdo,
        private string $table,
    ) {
    }

    function getTableName(): string
    {
        if (str_contains($this->table, '.')) {
            $table_name = explode('.', $this->table);
            $table_name[1] = '`' . $table_name[1] . '`';
            return implode('.', $table_name);
        } else {
            return $this->table = '`' . $this->table . '`';
        }
    }

    /**
     * Methods for getting information about Tables
     */

    function missing()
    {
        $result = DB::table('INFORMATION_SCHEMA.TABLES')
            ->whereEquals('TABLE_SCHEMA', DBNAME)
            ->whereEquals('TABLE_NAME', $this->table)
            ->all(['COUNT(TABLE_NAME) AS table_count']);

        return $result[0]->table_count === 0;
    }

    function exists()
    {
        return !$this->missing();
    }

    function hasColumns()
    {
        if (!$this->table_columns) $this->loadColumns();
        return $this->table_columns;
    }

    /**
     * Methods for getting information about Columns
     */
    private  function loadColumns()
    {
        $result = DB::table('information_schema.COLUMNS')
            ->whereEquals('TABLE_SCHEMA', DBNAME)
            ->whereEquals('TABLE_NAME', $this->table)
            ->sortyBy('ORDINAL_POSITION')
            ->all(['COLUMN_NAME', 'DATA_TYPE', 'COLUMN_TYPE', 'COLUMN_KEY', 'CHARACTER_MAXIMUM_LENGTH', 'IS_NULLABLE']);

        $this->table_columns = array_map(
            fn (object $column): Column => new Column($column),
            $result
        );
    }

    function getColumns(): array
    {
        if (!$this->table_columns) $this->loadColumns();
        return $this->table_columns;
    }

    function getPrimaryKeyColumn()
    {
        if ($this->table_columns) {
            return array_filter($this->table_columns, fn ($column) => $column->primary)[0]->getName();
        }
        return DB::table("information_schema.COLUMNS")
            ->whereEquals('TABLE_SCHEMA', DBNAME)
            ->whereEquals('TABLE_NAME', $this->table)
            ->whereEquals('COLUMN_KEY', 'PRI')
            ->first(["COLUMN_NAME"])
            ->COLUMN_NAME;
    }

    /**
     * Methods for database queries
     */
    function where(string $column, string $operator, mixed $value)
    {
        $this->conditions[] = [$column, $operator, $value];
        return $this;
    }

    function whereEquals(string $column, mixed $value)
    {
        return $this->where($column, '=', $value);
    }

    function sortyBy(string $column, bool $asc = true)
    {
        $this->sort = [$column, $asc ? 'ASC' : 'DESC'];
        return $this;
    }

    function limit(int $_limit)
    {
        $this->lim = $_limit;
        return $this;
    }

    private function stringifiedColumns(array $columns)
    {
        return implode(',', $columns);
    }

    private function stringifiedConditions()
    {
        $conditions_as_strings = array_map(
            fn (array $condition): string => $condition[0] . $condition[1] . ':condition_' . $condition[0],
            $this->conditions
        );

        return " WHERE " . implode(' AND ', $conditions_as_strings);
    }

    private function bindableParams(array $data)
    {
        $params = [];

        foreach ($data as $k => $v) $params[str_starts_with($k, ':') ? "input_$k" : ":input_$k"] = $v;

        return $params;
    }

    public function execute(array $inputs = []): array|bool
    {
        $query = $this->pdo->prepare($this->sql);

        /**
         * Load values from conditions
         */
        $values = [];

        // $values[':table_name'] = $this->table;

        if ($this->conditions)
            foreach ($this->conditions as [$column,, $value])
                $values[':condition_' . $column] = $value;

        if (!empty($inputs)) {
            foreach ($inputs as $k => $v) {
                if ($v instanceof Input) {
                    if ($v->getColumn()->isSet() && !empty($v->getValue())) {
                        $values[':input_' . $k] = implode(",", $v->getValue());
                    } else {
                        $values[':input_' . $k] = $v->getValue();
                    }
                } else {
                    $values[':input_' . $k] = is_array($v) ? implode(",", $v) : $v;
                }
            }
        }

        $query->execute($values);

        if (empty($inputs)) {
            $data = $query->fetchAll();
            return $data ? $data : [];
        } else {
            return $query->rowCount() > 0;
        }
    }

    function select(?array $columns = null): array
    {
        $this->sql .= 'SELECT ';
        $this->sql .= ($columns ? $this->stringifiedColumns($columns) : '*');
        $this->sql .=  ' FROM ';
        $this->sql .= $this->getTableName();
        if ($this->conditions) $this->sql .= $this->stringifiedConditions();
        if ($this->sort) $this->sql .= ' ORDER BY ' . $this->sort[0] . ' ' . $this->sort[1];
        if ($this->lim) $this->sql .= ' LIMIT ' . $this->lim;
        $this->sql .= ';';

        return $this->execute();
    }

    function all(?array $columns = null): array
    {
        return $this->select($columns);
    }

    function find(int $id): object|null
    {
        $result = $this->whereEquals($this->getPrimaryKeyColumn(), $id)->select();
        return empty($result) ? null : $result[0];
    }

    function first(?array $columns = null)
    {
        return $this->limit(1)->select($columns)[0];
    }

    function destroy()
    {
        $this->sql = "DELETE FROM ";
        $this->sql .= $this->getTableName();
        if ($this->conditions) $this->sql .= $this->stringifiedConditions();
        if ($this->sort) $this->sql .= ' ORDER BY ' . $this->sort[0] . ' ' . $this->sort[1];
        if ($this->lim) $this->sql .= ' LIMIT ' . $this->lim;
        $this->sql .= ';';

        $this->execute();
    }

    private function stringifiedSetters($values)
    {

        $setters_as_strings = [];

        foreach ($values as $k => $_) {
            $setters_as_strings[] = $k . '=' . ':input_' . $k;
        }

        return implode(' , ', $setters_as_strings);
    }

    function update($values)
    {
        $this->sql .= 'UPDATE ';
        $this->sql .= $this->getTableName();
        $this->sql .= ' SET ';
        $this->sql .= $this->stringifiedSetters($values);
        if ($this->conditions) $this->sql .= $this->stringifiedConditions();
        $this->sql .= ';';
        return $this->execute($values);
    }

    function insert($values)
    {
        $this->sql .= 'INSERT INTO ';
        $this->sql .= $this->getTableName();
        $this->sql .= '(' . $this->stringifiedColumns(array_keys($values)) . ')';
        $this->sql .= ' VALUES ';

        foreach ($values as $k => $_) $bindableValues[] = ":input_" . $k;

        $this->sql .= '(' . $this->stringifiedColumns($bindableValues) . ')';
        $this->sql .= ';';

        return $this->execute($values);
    }
}
