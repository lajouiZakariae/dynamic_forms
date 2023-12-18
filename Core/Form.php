<?php

namespace Core;

class Form {
    private ?string $table = null;

    private ?string $primaryKey = null;

    private string $mode = FormMode::CREATE;

    private array $columns = [];

    private array $entity = [];

    /** @var Input[] $inputs */
    private $inputs = [];

    private ?string $error = null;

    private function __construct(?string $table = null) {
        $this->table = $table;
        $this->load();
    }

    /**
     * FormMode setter
     * @var string $formMode
     * @return void
     */
    private function setMode(string $formMode): void {
        $this->mode = $formMode;
    }

    private function isEditMode(): bool {
        return $this->mode === FormMode::EDIT;
    }

    private function isPostMode(): bool {
        return $this->mode === FormMode::POST;
    }

    private function isUpdateMode(): bool {
        return $this->mode === FormMode::UPDATE;
    }

    private function load() {
        // if table missing render error
        if (tableMissing($this->table))
            return $this->error = '<div>Table ' . $this->table . ' Not Found</div>';

        $this->columns = getColumns($this->table);

        // if columns are messing render warning
        if ($this->hasNoColumns())
            return $this->error = '<div>Table ' . $this->table . ' has no column</div>';

        // get primary key
        $this->primaryKey = getPrimaryKey($this->table);

        // handle post request 
        if (isPost()) $this->handlePostRequest();

        // if edit form set edit mode
        if (isParam('action', 'edit') && paramExists($this->primaryKey)) $this->setMode(FormMode::EDIT);

        // if showing edit form  or updating get Data 
        if ($this->isEditMode() || $this->isUpdateMode()) $this->fetchEntity();
    }

    private function hasNoColumns(): bool {
        return empty($this->columns)
            || (count($this->columns) === 1 && $this->columns[0]->isPrimary());
    }

    /**
     * returns Column based on key
     * @param string $key
     * @return Column $column
     */
    private function getColumnByName(string $key): Column {
        $filtered = array_filter($this->columns, fn (Column $column) => $column->getName() === $key);

        return $filtered[array_key_first($filtered)];
    }

    private function fetchEntity() {
        $entity = find($this->table, param($this->primaryKey));

        if (!$entity) return $this->error = "<div class=\"alert alert-danger\">Item Not Found</div>";

        foreach ($entity as $key => $value) {
            $this->entity[$key] = new Input($this->getColumnByName($key), $value);
        }
    }

    private function populateInputs(): void {
        foreach ($this->columns as $col) {
            if (!$col->isPrimary()) {
                $this->inputs[$col->getName()] = new Input($col, input($col->getName()));
            }
        }
    }

    private function handlePostRequest() {
        $this->populateInputs();

        whenParam('action', 'edit', function () {
            $this->setMode(FormMode::UPDATE);
            $this->handleEditing();
        });

        whenParam('action', 'create', function () {
            $this->setMode(FormMode::POST);
            $this->handleCreation();
        });
    }

    private function handleEditing() {
        /**
         * Updating resource in database
         */
        $inputs = [];

        foreach ($this->inputs as $key => $input) {
            if ($input->getColumn()->isSet())
                $inputs[$key] = $input->getColumn()->stringifySetValues($input->getValue());
            else $inputs[$key] = $input->getValue();
        }

        update($this->table, $inputs, param($this->primaryKey));
        $this->redirect();
    }

    private function handleCreation() {
        /**
         * Inserting to database
         */
        $inputs = [];

        foreach ($this->inputs as $key => $input) {
            $inputs[$key] = $input->getValue();
        }

        insert($this->table, $inputs);
        $this->redirect();
    }

    private function redirect(): void {
        $page = Session::get("page");
        if ($page) redirect("index.php?page=$page");
        else redirect("index.php");
    }

    /**
     * Cenerates the table html with data
     * @return ?string
     */
    public function _html(): string {
        if ($this->error) return $this->error;

        $url = 'post.php';

        if ($this->isEditMode() || $this->isUpdateMode())
            $url .= '?action=edit&' . $this->primaryKey . '=' . $this->entity[$this->primaryKey]->getValue();
        else
            $url .= '?action=create';


        $values = null;

        if ($this->isEditMode()) $values = $this->entity;

        if ($this->isUpdateMode() || $this->isPostMode()) $values = $this->inputs;

        return view('form', [
            'primary_key' => $this->primaryKey,
            'columns' => $this->columns,
            'post_url' => $url,
            'values' => $values
        ]);
    }

    /**
     * @param ?string $table
     * @return ?string
     */
    public static function html(?string $table = null): string {
        return (new self($table))->_html();
    }

    /**
     * Outputs the table
     * @param ?string $table
     * @return void
     */
    public static function render(?string $table = null): void {
        echo (new self($table))->_html();
    }

    /**
     * Write html to a file
     * @param ?string $table
     * @param ?string $filename
     * @return int|null
     */
    public static function writeFile(?string $table, string $filename = 'draft.php'): int|null {
        $content = (new self($table))->_html();
        return file_put_contents($filename, $content);
    }
}
