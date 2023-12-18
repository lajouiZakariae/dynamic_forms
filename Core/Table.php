<?php

namespace Core;

class Table {
    protected static $instances = [];

    protected string $table;

    protected ?string $primaryKey = null;

    /** @var Column[] $columns */
    protected $columns = [];

    protected ?int $currentPage = null;

    protected ?string $error = null;

    private function __construct(string $table) {
        $this->table = $table;
        $this->load();
    }

    protected function hasNoColumns(): bool {
        return (empty($this->columns) || (count($this->columns) === 1 && $this->columns[0]->isPrimary()));
    }

    protected function load() {
        if (tableMissing($this->table))
            return $this->error = '<div>Table ' . $this->table . ' Not Found</div>';

        $this->primaryKey = getPrimaryKey($this->table);

        $this->columns = getColumns($this->table);

        if (param('action') === 'delete' && paramExists($this->primaryKey)) {
            $this->destroyItem();
        }

        if ($this->hasNoColumns())
            return $this->error = '<div>Table ' . $this->table . ' has no column</div>';
    }

    private function destroyItem(): void {
        DB::sql("DELETE FROM `{$this->table}` WHERE {$this->primaryKey}= ?;", [param($this->primaryKey)]);
    }

    public function _html(): string {
        if ($this->error) return $this->error;

        $paginator = paginate($this->table, per_page: 8);

        if ($paginator->getLast()) {
            $this->currentPage = $paginator->getCurrentPage();
            Session::set('page', $this->currentPage);
        }

        return view('table', [
            'columns' => $this->columns,
            'primary_key' => $this->primaryKey,
            'data' => $paginator->getData(),
            'current_page' => $paginator->getCurrentPage(),
            'last' => $paginator->getLast(),
            'previous_url' => $paginator->getPrevious() ? $paginator->getPreviousUrl() : null,
            'next_url' => $paginator->getNext() ? $paginator->getNextUrl() : null,
            'links' => empty($paginator->getLinks()) ? null : $paginator->getLinks(),
        ]);
    }

    public static function instance(?string $table): Table {
        if (is_null($table)) $table =  scriptParentDir($_SERVER['SCRIPT_FILENAME']);

        if (array_key_exists($table, self::$instances)) return self::$instances[$table];

        $instance = new self($table);

        self::$instances[$table] = $instance;

        return $instance;
    }

    public static function html(?string $table = null): string {
        return self::instance($table)->_html();
    }

    public static function writeFile(?string $table = null, string $filename = 'draft.php'): int|false {
        $content =  self::instance($table)->_html();
        return file_put_contents($filename, $content);
    }

    public static function render(?string $table = null): void {
        echo self::instance($table)->_html();
    }
}
