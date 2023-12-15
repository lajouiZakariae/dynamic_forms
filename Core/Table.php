<?php

namespace Core;

class Table extends Renderer
{
    private static ?string $primaryKey = null;

    private static ?string $table = null;

    private static function icon(string $name, string $color)
    {
        return self::el('i', [
            'class' => 'fas fa-' . $name . ' text-light bg-' . $color . ' d-flex justify-content-center align-items-center',
            'style' => 'border-radius:50%; width:32px;height:32px;cursor:pointer;'
        ]);
    }

    private static function destroyItem()
    {
        DB::table(self::$table)
            ->whereEquals(self::$primaryKey, Request::param(self::$primaryKey))
            ->destroy();
    }

    /**
     * @param Column[] $columns 
     * @return string
     **/
    private static function headers($columns): string
    {
        $html = '  <thead>';
        $html .= '      <tr>';

        foreach ($columns as  $column) $html .= ' <th>' . titleCase($column->getName()) . '</th>';

        $html .= '          <th colspan="2">Actions</th>';
        $html .= '      </tr>';
        $html .= '  </thead>';
        return $html;
    }

    private static function row(object $item): string
    {
        $table_data = [];

        foreach ($item as  $value) {
            $table_data[] = self::el('td', children: $value);
        }

        $table_data[] = self::el(
            'td',
            children: self::el(
                'a',
                ['href' => 'index.php?action=delete&' . self::$primaryKey . '=' . $item->{self::$primaryKey}],
                self::icon(name: 'trash', color: 'danger'),
            )
        );

        $table_data[] = self::el(
            'td',
            children: self::el(
                'a',
                ['href' => 'post.php?action=edit&' . self::$primaryKey . '=' . $item->{self::$primaryKey}],
                self::icon(name: 'pencil', color: 'primary'),
            )
        );

        return self::el('tr', children: $table_data);
    }

    static function html(?string $table = null): string
    {
        self::$table = $table;

        self::$table = $table ? $table : scriptParentDir($_SERVER['SCRIPT_FILENAME']);

        self::$primaryKey = DB::table(self::$table)->getPrimaryKeyColumn();

        if (Request::param('action') === 'delete' && Request::paramExists(self::$primaryKey)) {
            self::destroyItem(self::$table);
        };

        $rows = array_map(fn ($item) => self::row($item), DB::table(self::$table)->all());

        return self::el('div', children: [
            self::el(
                'a',
                [
                    'class' => 'btn btn-primary', 'href' => 'post.php'
                ],
                'Add'
            ),
            self::el('table', ['class' => 'table text-center'], [
                self::headers(DB::table(self::$table)->getColumnsWithTypes()), // table html headers
                self::el('tbody', children: $rows) // table html body 
            ]),
        ]);
    }

    static function writeFile(?string $table = null, string $filename = 'draft.php'): void
    {
        file_put_contents($filename, self::html($table));
    }

    static function render(?string $table = null): void
    {
        echo self::html($table);
    }
}
