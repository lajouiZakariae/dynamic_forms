<?php

namespace Core;

class Form extends Renderer
{
    private static ?string $table = null;

    private static ?string $primaryKey = null;

    /**
     * Object to edit
     */
    private static ?object $entity = null;

    /**
     * Object to add
     */
    private static array $inputs =  [];

    private static function inputZone($column): string
    {
        return self::el('div', ['class' => 'row mb-2'], [
            self::el('div', ['class' => 'col-3'], [
                self::el(
                    'label',
                    ['for' => $column->name],
                    titleCase($column->name)
                )
            ]),
            self::el('div', ['class' => 'col-9'], [
                self::el(
                    'input',
                    [
                        'class' => 'form-control',
                        'name' => $column->name,
                        'id' => $column->name,
                        'value' => self::$entity
                            ? self::$entity->{$column->name} // edit values
                            : (!empty(self::$inputs)
                                ? self::$inputs[$column->name] //input values
                                : '')
                    ],
                )
            ]),
        ]);
    }

    /**
     * Get Entity Information to edit
     */
    private static function fetchEntity(): void
    {
        if (Request::isParam('action', 'edit') && Request::paramExists(self::$primaryKey)) {
            self::$entity = DB::table(self::$table)->find(Request::param(self::$primaryKey));
        }
    }

    private static function handlePostRequest()
    {

        if (Request::isParam('action', 'edit')) {
            $values = [];
            foreach (self::$entity as $key => $_) {
                if ($key != self::$primaryKey)
                    $values[$key] = Request::input($key);
            }
            DB::table(self::$table)->whereEquals(self::$primaryKey, Request::param(self::$primaryKey))->update($values);
            header('Location: index.php');
            exit;
            self::$entity = DB::table(self::$table)->find(Request::param(self::$primaryKey));
        } elseif (Request::isParam('action', 'create')) {
            foreach ($_POST as $k => $v) self::$inputs[$k] = $v;

            DB::table(self::$table)->insert(self::$inputs);
            header('Location: index.php');
            exit;
        }
    }

    static function html(?string $table = null): string
    {
        self::$table = $table ? $table : scriptParentDir($_SERVER['SCRIPT_FILENAME']);

        self::$primaryKey = DB::table(self::$table)->getPrimaryKeyColumn();

        self::fetchEntity();

        if (Request::isPost()) self::handlePostRequest();

        $inputs = array_map(
            function ($column) {
                $isPrimaryKey = $column->name === self::$primaryKey;

                return  $isPrimaryKey // Skip primary key
                    ? '' : self::inputZone($column);
            },
            DB::table(self::$table)->getColumnsWithTypes()
        );

        $button = self::el(
            'div',
            ['class' => 'row'],
            self::el(
                'div',
                ['class' => 'col-9 ms-auto'],
                self::el('button', ['type' => 'submit', 'class' => 'btn btn-primary w-100'], 'Save')
            )
        );

        $html = self::el(
            'form',
            [
                'action' => "post.php?" . (
                    self::$entity
                    ? 'action=edit&' . self::$primaryKey . '=' . self::$entity->{self::$primaryKey}
                    : 'action=create'
                ),
                'method' => 'post',
                'style' => 'max-width:650px;',
            ],
            array_merge($inputs, [$button])
        );

        return $html;
    }

    static function render(?string $table = null): void
    {
        echo self::html($table);
    }
}
