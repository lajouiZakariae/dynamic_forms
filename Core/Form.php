<?php

namespace Core;

class Form extends Renderer
{
    protected ?string $table = null;

    protected ?string $primaryKey = null;

    protected string $mode = FormMode::CREATE;

    protected array $columns = [];

    private array $entity = [];

    private array $inputs = [];

    protected ?string $error = null;

    private function __construct(?string $table)
    {
        $this->table = $table ? $table : scriptParentDir($_SERVER['SCRIPT_FILENAME']);
        $response = $this->load();
        if (!is_null($response)) $this->error = $response;
    }

    private function setMode(string $formMode): void
    {
        $this->mode = $formMode;
    }

    private function isEditMode(): bool
    {
        return $this->mode === FormMode::EDIT;
    }

    private function isCreateMode(): bool
    {
        return $this->mode === FormMode::CREATE;
    }

    private function isPostMode(): bool
    {
        return $this->mode === FormMode::POST;
    }

    private function isUpdateMode(): bool
    {
        return $this->mode === FormMode::UPDATE;
    }

    private function getInputType(Column $column): string
    {
        $type = 'text';

        if ($column->getName() === 'email') $type = 'email';
        if ($column->getName() === 'password') $type = 'password';

        if ($column->getType() === 'date') $type = 'date';
        elseif ($column->getType() === 'time') $type = 'time';
        elseif ($column->getType() === 'datetime') $type = 'datetime-local';

        return $type;
    }

    private function selectHtml(Column $column): string
    {
        $options = [];

        foreach ($column->getAllowedValues() as $value) {
            $options[] = $this->el(
                'option',
                [
                    'value' => $value,
                    'selected' => $this->isEditMode()
                        ? $this->entity[$column->getName()]->getValue() === $value // edit values
                        : (
                            ($this->isPostMode() || $this->isUpdateMode())
                            ? $this->inputs[$column->getName()]->getValue() === $value //input values
                            : false
                        )
                ],
                titleCase($value)
            );
        }

        return $this->el(
            'select',
            ['class' => 'form-select', 'name' => $column->getName()],
            $options
        );
    }

    private function checkBoxesHtml(Column $column): array
    {
        $options = [];

        foreach ($column->getAllowedValues() as $value) {
            $checked = false;

            if ($this->isEditMode())
                $checked = $this->entity[$column->getName()]->isAllowedValue($value);
            elseif ($this->isPostMode() || $this->isUpdateMode())
                $checked = $this->inputs[$column->getName()]->isAllowedValue($value);

            $options[] = $this->el(
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

            $options[] = $this->el(
                'label',
                [],
                titleCase($value)
            );
        }

        return $options;
    }

    private function textareaHtml(Column $column): string
    {
        return $this->el(
            'textarea',
            [
                'class' => 'form-control', 'name' => $column->getName(),
            ],
            $this->isEditMode()
                ? $this->entity[$column->getName()]->getValue() // edit values
                : (
                    ($this->isPostMode() || $this->isUpdateMode())
                    ? $this->inputs[$column->getName()]->getValue() //input values
                    : ''
                )
        );
    }

    private function inputHtml(Column $column): string
    {
        return $this->el(
            "input",
            [
                'type' => $this->getInputType($column),
                'class' => 'form-control',
                'name' => $column->getName(),
                'id' => $column->getName(),
                'value' => $this->isEditMode()
                    ? $this->entity[$column->getName()]->getValue() // edit values
                    : (
                        ($this->isPostMode() || $this->isUpdateMode())
                        ? $this->inputs[$column->getName()]->getValue() //input values
                        : false
                    )
            ],
            self_closing: true
        );
    }

    private function inputZone(Column $column): string
    {
        $input = '';

        if ($column->isEnum()) {
            $input = $this->selectHtml($column);
        } elseif ($column->isSet()) {
            $input = $this->checkBoxesHtml($column);
        } elseif ($column->isText()) {
            $input = $this->textareaHtml($column);
        } else {
            $input = $this->inputHtml($column);
        }

        return $this->el('div', ['class' => 'row mb-2'], [
            $this->el('div', ['class' => 'col-3'], [
                $this->el(
                    'label',
                    ['for' => $column->getName()],
                    titleCase($column->getName())
                )
            ]),
            $this->el(
                'div',
                ['class' => 'col-9'],
                is_array($input) ? $input : [$input]
            ),
        ]);
    }

    protected function getColumnByName(string $key): Column
    {
        $filtered = array_filter($this->columns, fn (Column $column) => $column->getName() === $key);

        return $filtered[array_key_first($filtered)];
    }

    protected function fetchEntity(): void
    {
        foreach (DB::table($this->table)->find(Request::param($this->primaryKey)) as $key => $value) {
            $this->entity[$key] = new Input($this->getColumnByName($key), $value);
        }
    }

    protected function populateInputs(): void
    {
        foreach ($this->columns as $col) {
            if (!$col->isPrimary()) {
                $this->inputs[$col->getName()] = new Input($col, Request::input($col->getName()));
            }
        }
    }

    protected function handleEditing()
    {
        $this->populateInputs();

        /**
         * Updating resource in database
         */

        // DB::table($this->table)->whereEquals($this->primaryKey, Request::param($this->primaryKey))->update($values);
        // redirect('index.php');
    }

    private function handleCreation()
    {
        $this->populateInputs();
        /**
         * Inserting to database
         */

        // DB::table($this->table)->insert($this->inputs);
        // redirect('index.php');
    }

    private function handlePostRequest()
    {
        Request::whenParam('action', 'edit', function () {
            $this->setMode(FormMode::UPDATE);
            $this->handleEditing();
        });

        Request::whenParam('action', 'create', function () {
            $this->setMode(FormMode::POST);
            $this->handleCreation();
        });
    }

    protected function hasNoColumns(): bool
    {
        return empty($this->columns)
            || (count($this->columns) === 1 && $this->columns[0]->isPrimary());
    }

    protected function load(): ?string
    {
        if (DB::table($this->table)->missing())
            return $this->renderError('Table Not Found');

        $this->columns = DB::table($this->table)->getColumns();

        if ($this->hasNoColumns())
            return $this->renderWarning('Table ' . $this->table . ' has no column');

        $this->primaryKey = DB::table($this->table)->getPrimaryKeyColumn();

        if (Request::isPost()) $this->handlePostRequest();

        if (Request::isGet() && Request::isParam('action', 'edit') && Request::paramExists($this->primaryKey)) $this->setMode(FormMode::EDIT);

        if ($this->isEditMode() || $this->isUpdateMode()) $this->fetchEntity();

        return null;
    }

    public function _html(): string
    {
        if ($this->error) return $this->error;

        $inputs = array_map(
            function (Column $column) {
                $isPrimaryKey = $column->getName() === $this->primaryKey;

                return  $isPrimaryKey
                    ? '' : $this->inputZone($column);
            },
            $this->columns
        );

        $button = $this->el(
            'div',
            ['class' => 'row'],
            $this->el(
                'div',
                ['class' => 'col-9 ms-auto'],
                $this->el('button', ['type' => 'submit', 'class' => 'btn btn-primary w-100'], 'Save')
            )
        );

        $html = $this->el(
            'form',
            [
                'action' => "post.php?" . (
                    $this->isEditMode() || $this->isUpdateMode()
                    ? 'action=edit&' . $this->primaryKey . '=' . $this->entity[$this->primaryKey]->getValue()
                    : 'action=create'
                ),
                'method' => 'post',
                'style' => 'max-width:650px;',
            ],
            array_merge($inputs, [$button])
        );

        return $html;
    }

    public static function html(?string $table = null): string
    {
        return (new self($table))->_html();
    }

    public static function render(?string $table = null): void
    {
        echo (new self($table))->_html();
    }

    public static function writeFile(?string $table, string $filename = 'draft.php'): int|null
    {
        $content = (new self($table))->_html();
        return file_put_contents($filename, $content);
    }
}
