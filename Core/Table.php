<?php

namespace Core;

class Table extends Renderer
{
    protected string $table;

    protected static $instances = [];

    protected ?string $primaryKey = null;

    /** @var Column[] $columns */
    protected $columns = [];

    protected ?string $error = null;

    private function __construct(string $table)
    {
        $this->table = $table;
        $this->load();
    }

    private function icon(string $name, string $color): string
    {
        return $this->el('i', [
            'class' => 'fas fa-' . $name . ' text-light bg-' . $color . ' d-flex justify-content-center align-items-center',
            'style' => 'border-radius:50%; width:32px;height:32px;cursor:pointer;'
        ]);
    }

    private function destroyItem(): void
    {
        DB::table($this->table)
            ->whereEquals($this->primaryKey, Request::param($this->primaryKey))
            ->destroy();
    }

    private function headers($columns): string
    {
        $headers =  array_map(
            fn (Column $column) => $this->el('th', children: titleCase($column->getName())),
            $columns,
        );

        $headers[] = $this->el('th', ['colspan' => 2], 'Actions');

        return $this->el(
            'thead',
            children: $this->el("tr", children: $headers)
        );

        // $html = '  <thead>';
        // $html .= '      <tr>';

        // foreach ($columns as $column) {
        //     $html .= ' <th>' . titleCase($column->getName()) . '</th>';
        // }

        // $html .= '          <th colspan="2">Actions</th>';
        // $html .= '      </tr>';
        // $html .= '  </thead>';
        // return $html;
    }

    private function row(object $item): string
    {
        $table_data = [];

        foreach ($item as $value) {
            $table_data[] = $this->el('td', children: $value);
        }

        $table_data[] = $this->el(
            'td',
            children: $this->el(
                'a',
                ['href' => 'index.php?action=delete&' . $this->primaryKey . '=' . $item->{$this->primaryKey}],
                $this->icon(name: 'trash', color: 'danger'),
            )
        );

        $table_data[] = $this->el(
            'td',
            children: $this->el(
                'a',
                ['href' => 'post.php?action=edit&' . $this->primaryKey . '=' . $item->{$this->primaryKey}],
                $this->icon(name: 'pencil', color: 'primary'),
            )
        );

        return $this->el('tr', children: $table_data);
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

    public function _html(): string
    {
        if ($this->error) return $this->error;

        $rows = array_map(fn ($item) => $this->row($item), DB::table($this->table)->all());

        return $this->el('div', children: [
            $this->el(
                'a',
                [
                    'class' => 'btn btn-primary', 'href' => 'post.php'
                ],
                'Add'
            ),

            $this->el('table', ['class' => 'table text-center'], [

                $this->headers(DB::table($this->table)->getColumns()), // table html headers

                $this->el(
                    'tbody',
                    children: empty($rows)
                        ? $this->el('tr', children: [
                            $this->el('td', ['colspan' => count($this->columns) + 2], $this->renderWarning('Empty Table'))
                        ])
                        : $rows
                ) // table html body 
            ]),
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
