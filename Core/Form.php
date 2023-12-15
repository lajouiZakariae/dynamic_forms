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

    private static function getInputType(Column $column)
    {
        $type = 'text';

        if ($column->getName() === 'email') $type = 'email';
        if ($column->getName() === 'password') $type = 'password';

        if ($column->getType() === 'date') $type = 'date';
        elseif ($column->getType() === 'time') $type = 'time';
        elseif ($column->getType() === 'datetime') $type = 'datetime-local';

        return $type;
    }

    /**
     * Genrating inputs as HTML 
     */

    private static function selectHtml(Column $column): string
    {
        $options = [self::el('option', children: 'Choose')];

        foreach ($column->getAllowedValues() as $value) {
            $options[] = self::el(
                'option',
                [
                    'value' => $value,
                    'selected' => self::$entity
                        ? self::$entity->{$column->getName()} === $value // edit values
                        : (
                            !empty(self::$inputs)
                            ? self::$inputs[$column->getName()] === $value //input values
                            : false
                        )
                ],
                titleCase($value)
            );
        }

        return self::el(
            'select',
            ['class' => 'form-select', 'name' => $column->getName()],
            $options
        );
    }

    private static function checkBoxesHtml(Column $column): array
    {
        $options = [];

        foreach ($column->getAllowedValues() as $value) {
            $options[] = self::el(
                'input',
                [
                    'type' => 'checkbox',
                    'class' => 'form-checkbox',
                    'name' => $column->getName() . '[]',
                    'value' => $value,
                    'checked' => self::$entity
                        ? self::$entity->{$column->getName()} === $value // edit values
                        : (
                            !empty(self::$inputs)
                            ? self::$inputs[$column->getName()] === $value //input values
                            : false
                        )
                ],
                self_closing: true,
            );
            $options[] = self::el(
                'label',
                [],
                titleCase($value)
            );
        }

        return $options;
    }

    private static function textareaHtml(Column $column): string
    {
        return self::el(
            'textarea',
            [
                'class' => 'form-control', 'name' => $column->getName(),
            ],
            self::$entity
                ? self::$entity->{$column->getName()} // edit values
                : (
                    !empty(self::$inputs)
                    ? self::$inputs[$column->getName()] //input values
                    : ''
                )
        );
    }

    private static function inputHtml(Column $column): string
    {
        return self::el(
            "input",
            [
                'type' => self::getInputType($column),
                'class' => 'form-control',
                'name' => $column->getName(),
                'id' => $column->getName(),
                'value' => self::$entity
                    ? self::$entity->{$column->getName()} // edit values
                    : (
                        !empty(self::$inputs)
                        ? self::$inputs[$column->getName()] //input values
                        : false
                    )
            ],
            self_closing: true
        );
    }

    private static function inputZone(Column $column): string
    {
        $input = '';

        if ($column->isEnum()) {
            $input = self::selectHtml($column);
        } elseif ($column->isSet()) {
            $input = self::checkBoxesHtml($column);
        } elseif ($column->isText()) {
            $input = self::textareaHtml($column);
        } else {
            $input = self::inputHtml($column);
        }

        return self::el('div', ['class' => 'row mb-2'], [
            self::el('div', ['class' => 'col-3'], [
                self::el(
                    'label',
                    ['for' => $column->getName()],
                    titleCase($column->getName())
                )
            ]),
            self::el(
                'div',
                ['class' => 'col-9'],
                is_array($input) ? $input : [$input]
            ),
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

    /**
     * Request Handling
     */

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

    /**
     * Form Creation
     */

    static function html(?string $table = null): string
    {
        self::$table = $table ? $table : scriptParentDir($_SERVER['SCRIPT_FILENAME']);

        self::$primaryKey = DB::table(self::$table)->getPrimaryKeyColumn();

        $is_edit_form = Request::isParam('action', 'edit') && Request::paramExists(self::$primaryKey);

        if ($is_edit_form) self::fetchEntity();

        if (Request::isPost()) self::handlePostRequest();

        /**
         * Start Rendering
         */

        /** @var Column[] $columns  */
        $columns = DB::table(self::$table)->getColumnsWithTypes();

        $inputs = array_map(
            function (Column $column) {
                // $column->isPrimary()
                $isPrimaryKey = $column->getName() === self::$primaryKey;

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
        file_put_contents($filename, self::html($table));
    }
}
