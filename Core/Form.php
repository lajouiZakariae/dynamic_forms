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
        $this->load();
    }

    protected function load()
    {
        if (DB::table($this->table)->missing())
            return $this->error =  $this->renderError('Table' . $this->table . ' Not Found');

        $this->columns = DB::table($this->table)->getColumns();

        if ($this->hasNoColumns())
            return $this->error = $this->renderWarning('Table ' . $this->table . ' has no column');

        $this->primaryKey = DB::table($this->table)->getPrimaryKeyColumn();

        if (Request::isPost()) $this->handlePostRequest();

        if (Request::isGet() && Request::isParam('action', 'edit') && Request::paramExists($this->primaryKey)) $this->setMode(FormMode::EDIT);

        if ($this->isEditMode() || $this->isUpdateMode()) $this->fetchEntity();
    }

    /**
     * FormMode setter
     * @var string $formMode
     * @return void
     */
    private function setMode(string $formMode): void
    {
        $this->mode = $formMode;
    }

    private function isEditMode(): bool
    {
        return $this->mode === FormMode::EDIT;
    }

    private function isPostMode(): bool
    {
        return $this->mode === FormMode::POST;
    }

    private function isUpdateMode(): bool
    {
        return $this->mode === FormMode::UPDATE;
    }

    /**
     * returns Column based on key
     * @param string $key
     * @return Column $column
     */
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

        // DB::table($this->table)
        //     ->whereEquals($this->primaryKey, Request::param($this->primaryKey))
        //     ->update($this->inputs);

        $page = Session::get("page", 1);
        // redirect('index.php' . ($page ? "?page=$page" : ''));
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

    /**
     * Cenerates the table html with data
     * @return ?string
     */
    public function _html(): string
    {
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
    public static function html(?string $table = null): string
    {
        return (new self($table))->_html();
    }

    /**
     * Outputs the table
     * @param ?string $table
     * @return void
     */
    public static function render(?string $table = null): void
    {
        echo (new self($table))->_html();
    }

    /**
     * Write html to a file
     * @param ?string $table
     * @param ?string $filename
     * @return int|null
     */
    public static function writeFile(?string $table, string $filename = 'draft.php'): int|null
    {
        $content = (new self($table))->_html();
        return file_put_contents($filename, $content);
    }
}
