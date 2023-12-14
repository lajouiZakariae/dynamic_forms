<?php

namespace Core;

use Stringable;


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

    private static array $numeric_types = ['tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'bit', 'float', 'double', 'decimal'];

    private static array $big_text_types = ['tinytext',  'mediumtext', 'text', 'longtext'];



    private static function isNumeric($type): bool
    {
        return in_array($type, self::$numeric_types);
    }

    private static function isLongTextType($type): bool
    {
        return in_array($type, self::$big_text_types);
    }

    private static function getInputType($column)
    {
        if ($column->name === 'email') return 'email';
        if ($column->name === 'password') return 'password';

        return match ($column->type) {
            self::isLongTextType($column->type) => 'text',
            'date' => 'date',
            'time', 'timestamp' => 'time',
            'datetime' => 'datetime-local',
            'varchar', 'char', self::isNumeric($column->type) => "text",
            default => 'text'
        };
    }

    private static function selectHtml(object $column): string
    {
        dump($column);

        $options = [self::el('option', children: 'Choose')];

        foreach ($column->possible_values as $value) {
            $options[] = self::el(
                'option',
                [
                    'value' => $value,
                    'selected' => self::$entity
                        ? self::$entity->{$column->name} === $value // edit values
                        : (
                            !empty(self::$inputs)
                            ? self::$inputs[$column->name] === $value //input values
                            : false
                        )
                ],
                titleCase($value)
            );
        }

        return self::el(
            'select',
            ['class' => 'form-select', 'name' => $column->name],
            $options
        );
    }

    private static function textareaHtml(object $column): string
    {
        return self::el(
            'textarea',
            [
                'class' => 'form-control', 'name' => $column->name,
            ],
            self::$entity
                ? self::$entity->{$column->name} // edit values
                : (
                    !empty(self::$inputs)
                    ? self::$inputs[$column->name] //input values
                    : ''
                )
        );
    }

    private static function inputHtml(object $column): string
    {
        return self::el(
            "input",
            [
                'type' => self::getInputType($column),
                'class' => 'form-control',
                'name' => $column->name,
                'id' => $column->name,
                'value' => self::$entity
                    ? self::$entity->{$column->name} // edit values
                    : (
                        !empty(self::$inputs)
                        ? self::$inputs[$column->name] //input values
                        : ''
                    )
            ],
            self_closing: true
        );
    }

    private static function inputZone($column): string
    {
        dump($column);

        $input = '';

        if ($column->type === "enum") {
            $input = self::selectHtml($column);
        } elseif (self::isLongTextType($column->type)) {
            $input = self::textareaHtml($column);
        } else {
            $input = self::inputHtml($column);
        }


        return self::el('div', ['class' => 'row mb-2'], [
            self::el('div', ['class' => 'col-3'], [
                self::el(
                    'label',
                    ['for' => $column->name],
                    titleCase($column->name)
                )
            ]),
            self::el('div', ['class' => 'col-9'], [
                $input
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

    private static function handleEditing()
    {

        $values = [];

        // Updating an existing Resource
        foreach (self::$entity as $key => $_) {
            if ($key != self::$primaryKey)
                $values[$key] = Request::input($key);
        }

        DB::table(self::$table)->whereEquals(self::$primaryKey, Request::param(self::$primaryKey))->update($values);
        redirect('index.php');
        // self::$entity = DB::table(self::$table)->find(Request::param(self::$primaryKey));
    }

    private static function handleCreation()
    {
        // Creating a new Resource
        foreach ($_POST as $k => $v) self::$inputs[$k] = $v;

        DB::table(self::$table)->insert(self::$inputs);
        redirect('index.php');
    }

    private static function handlePostRequest()
    {
        if (Request::isParam('action', 'edit')) {
            self::handleEditing();
        } elseif (Request::isParam('action', 'create')) {
            self::handleCreation();
        }
    }

    static function html(?string $table = null): string
    {
        self::$table = $table ? $table : scriptParentDir($_SERVER['SCRIPT_FILENAME']);

        self::$primaryKey = DB::table(self::$table)->getPrimaryKeyColumn();

        self::fetchEntity();

        if (Request::isPost()) self::handlePostRequest();

        /**
         * Start Rendering
         */

        $columns = DB::table(self::$table)->getColumnsWithTypes();

        $inputs = array_map(
            function ($column) {
                $isPrimaryKey = $column->name === self::$primaryKey;

                return  $isPrimaryKey // Skip primary key
                    ? '' : self::inputZone($column);
            },
            $columns
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

    static function writeFile(?string $table = null, string $filename = 'draft.php'): void
    {
        $content = self::html($table);
        file_put_contents($filename, $content);
    }
}
