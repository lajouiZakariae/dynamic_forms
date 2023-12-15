<?php

namespace Core;

class Form extends Renderer
{
    protected static ?string $table = null;

    protected static ?string $primaryKey = null;

    protected static bool $editMode = false;

    /** @var FormMode $mode  */
    protected static $mode = FormMode::SHOW;

    /** @var Column[] $columns  */
    protected static $columns = [];

    /** @var Input[] $entity  */
    private static array $entity = [];

    /** @var Input[] $inputs  */
    private static $inputs =  [];


    private static function setMode(string $formMode): void
    {
        self::$mode = $formMode;
    }

    private static function isEditMode(): bool
    {
        return self::$mode === FormMode::EDIT;
    }

    private static function isCreateMode(): bool
    {
        return self::$mode === FormMode::CREATE;
    }

    private static function isShowMode(): bool
    {
        return self::$mode === FormMode::SHOW;
    }

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
        $options = [];

        foreach ($column->getAllowedValues() as $value) {
            $options[] = self::el(
                'option',
                [
                    'value' => $value,
                    'selected' => self::$editMode
                        ? self::$entity[$column->getName()]->getValue() === $value // edit values
                        : (
                            !empty(self::$inputs)
                            ? self::$inputs[$column->getName()]->getValue() === $value //input values
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
            $checked = false;

            if (self::isEditMode()) {
                $set_values = $column->normalizeSetValues(self::$entity[$column->getName()]->getValue());
                $checked = in_array($value, $set_values);
            } elseif (self::isCreateMode()) {
                dump(self::$inputs[$column->getName()]);
                $set_values = (self::$inputs[$column->getName()]->getValue());
                $checked = in_array($value, $set_values);
            }


            $options[] = self::el(
                'input',
                [
                    'type' => 'checkbox',
                    'class' => 'form-checkbox',
                    'name' => $column->getName() . '[]',
                    'value' => $value,
                    'checked' => $checked
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
            self::isEditMode()
                ? self::$entity[$column->getName()]->getValue() // edit values
                : (
                    self::isCreateMode()
                    ? self::$inputs[$column->getName()]->getValue() //input values
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
                'value' => self::isEditMode()
                    ? self::$entity[$column->getName()]->getValue() // edit values
                    : (
                        self::isCreateMode()
                        ? self::$inputs[$column->getName()]->getValue() //input values
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

    protected static function getColumnByName(string $key): Column
    {
        $filterd = array_filter(self::$columns, fn (Column $column) => $column->getName() === $key);

        return $filterd[array_key_first($filterd)];
    }

    /**
     * Get Entity Information to edit
     */
    private static function fetchEntity(): void
    {
        if (Request::isParam('action', 'edit') && Request::paramExists(self::$primaryKey)) {
            // self::$entity = ;
            foreach (DB::table(self::$table)->find(Request::param(self::$primaryKey)) as $key => $value) {
                self::$entity[$key] = new Input(self::getColumnByName($key), $value);
            }
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
        foreach (self::$columns as $col) {
            if (!$col->isPrimary()) {
                self::$inputs[$col->getName()] = new Input($col, Request::input($col->getName()));
            }
        }

        DB::table(self::$table)->insert(self::$inputs);

        redirect('index.php');
    }

    private static function handlePostRequest()
    {
        if (Request::isParam('action', 'edit')) {
            self::handleEditing();
        } elseif (Request::isParam('action', 'create')) {
            self::setMode(FormMode::CREATE);
            self::handleCreation();
        }
    }

    protected static function hasNoColumns(): bool
    {
        return (empty(self::$columns) || (count(self::$columns) === 1 && self::$columns[0]->isPrimary()));
    }
    /**
     * Set Up Values
     */
    protected static function load($table): null|string
    {
        self::$table = $table ? $table : scriptParentDir($_SERVER['SCRIPT_FILENAME']);
        self::$table = 'empty';

        if (DB::table(self::$table)->missing())
            return self::renderError('Table Not Found');

        self::$columns = DB::table(self::$table)->getColumns();

        if (self::hasNoColumns())
            return self::renderWarning('Table ' . self::$table . ' has no column');

        self::$primaryKey = DB::table(self::$table)->getPrimaryKeyColumn();

        if (Request::isParam('action', 'edit') && Request::paramExists(self::$primaryKey)) self::setMode(FormMode::EDIT);

        if (self::isEditMode()) self::fetchEntity();

        if (Request::isPost()) self::handlePostRequest();

        return null;
    }

    /**
     * Form Creation
     */
    static function html(?string $table = null): string
    {
        $response = self::load($table);

        if ($response) return $response;

        /**
         * Start Rendering
         */
        $inputs = array_map(
            function (Column $column) {
                // $column->isPrimary()
                $isPrimaryKey = $column->getName() === self::$primaryKey;

                return  $isPrimaryKey // Skip primary key
                    ? '' : self::inputZone($column);
            },
            self::$columns
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
                    !empty(self::$entity)
                    ? 'action=edit&' . self::$primaryKey . '=' . self::$entity[self::$primaryKey]->getValue()
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
