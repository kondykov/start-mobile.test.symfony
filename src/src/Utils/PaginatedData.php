<?php

namespace App\Utils;

readonly class PaginatedData
{
    public function __construct(
        private array $data,
        private int   $page,
        private int   $pageSize,
        private int   $total
    )
    {

    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getMetadata(): array
    {
        return [
            'total' => $this->total,
            'page' => $this->page,
            'pageSize' => $this->pageSize,
        ];
    }
}
