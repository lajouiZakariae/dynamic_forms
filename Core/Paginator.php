<?php

namespace Core;

class Paginator
{
    /** @var int[] $links */
    private $links = [];
    private ?int $current_page = null;

    public function __construct(
        private array $data,
        private ?int $last = null,
    ) {
        $this->current_page = (Request::paramExists('page') && Request::isParamInt('page'))
            ? Request::paramInteger('page')
            : 1;

        if ($this->last) {
            $this->generateLinks();
        }
    }

    protected function generateLinks()
    {
        $links = [];

        if (3 < $this->last && $this->last <= 5) $links = range(1, 5);

        if (5 < $this->last) {
            if ($this->current_page + 1 >= $this->last) {
                $links = range($this->last - 4, $this->last);
            } else if ($this->current_page - 1 <= 1) {
                $links = range(1, 5);
            } else {
                $links = range($this->current_page - 2, $this->current_page + 2);
            }
        }

        return $this->links = $links;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getCurrentPage(): ?int
    {
        return $this->current_page;
    }

    public function getLast(): ?int
    {
        return $this->last;
    }

    public function getNext(): ?int
    {
        return is_null($this->current_page)
            ? null
            : (($this->current_page === $this->last) ? null : $this->current_page + 1);
    }

    public function getPrevious(): ?int
    {
        return is_null($this->current_page)
            ? null
            : (($this->current_page === 1) ? null : $this->current_page - 1);
    }

    /**
     * @return int[]
     */
    public function getLinks()
    {
        return $this->links;
    }
}
