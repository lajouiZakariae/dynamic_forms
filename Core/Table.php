<?php

namespace Core;

class Table extends Renderer
{
    protected static $instances = [];

    protected string $table;

    protected ?string $primaryKey = null;

    /** @var Column[] $columns */
    protected $columns = [];

    protected ?int $currentPage = null;

    protected ?string $error = null;

    private function __construct(string $table)
    {
        $this->table = $table;
        $this->load();
    }

    protected function hasNoColumns(): bool
    {
        return (empty($this->columns) || (count($this->columns) === 1 && $this->columns[0]->isPrimary()));
    }

    protected function load()
    {
        if (DB::table($this->table)->missing())
            return $this->error = $this->renderError('Table ' . $this->table . ' Not Found');

        $this->primaryKey = DB::table($this->table)->getPrimaryKeyColumn();

        if (Request::param('action') === 'delete' && Request::paramExists($this->primaryKey)) {
            $this->destroyItem();
        }

        $this->columns = DB::table($this->table)->getColumns();

        if ($this->hasNoColumns())
            return $this->error = $this->renderWarning('Table ' . $this->table . ' has no column');
    }

    private function icon(string $name, string $color): string
    {
        $icon = '<i ';

        $icon .=    'class="fas fa-' . $name . ' text-light bg-' . $color . ' d-flex justify-content-center align-items-center"';

        $icon .=    'style="border-radius:50%; width:32px;height:32px;cursor:pointer;"';

        $icon .= '></i>';

        return $icon;
    }

    private function destroyItem(): void
    {
        DB::table($this->table)
            ->whereEquals($this->primaryKey, Request::param($this->primaryKey))
            ->destroy();
    }

    public function _html(): string
    {
        if ($this->error) return $this->error;

        $paginator = DB::table($this->table)->paginate(per_page: 8);

        if ($paginator->getCurrentPage()) {
            $this->currentPage = $paginator->getCurrentPage();

            Session::set('page', $this->currentPage);
        }


        return view('table', [
            'columns' => $this->columns,
            'data' => $paginator->getData(),
            'currentPage' => $paginator->getCurrentPage(),
            'previous_url' => $paginator->getPrevious() ? $paginator->getPreviousUrl() : null,
            'next_url' => $paginator->getNext() ? $paginator->getNextUrl() : null,
            'links' => empty($paginator->getLinks()) ? null : $paginator->getLinks(),
        ]);
    }

    public static function instance(?string $table): Table
    {
        if (is_null($table)) $table =  scriptParentDir($_SERVER['SCRIPT_FILENAME']);

        if (array_key_exists($table, self::$instances)) return self::$instances[$table];

        $instance = new self($table);

        self::$instances[$table] = $instance;

        return $instance;
    }

    public static function html(?string $table = null): string
    {
        return self::instance($table)->_html();
    }

    public static function writeFile(?string $table = null, string $filename = 'draft.php'): int|false
    {
        $content =  self::instance($table)->_html();
        return file_put_contents($filename, $content);
    }

    public static function render(?string $table = null): void
    {
        echo self::instance($table)->_html();
    }
}
