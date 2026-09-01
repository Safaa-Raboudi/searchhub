<?php

declare(strict_types=1);

namespace App\Document\Application\List;

use App\Document\Application\DocumentView;

final class ListDocumentsResult
{
    /** @var DocumentView[] */
    private array $items;

    private int $page;
    private int $limit;
    private int $total;

    /**
     * @param DocumentView[] $items
     */
    public function __construct(array $items, int $page, int $limit, int $total)
    {
        $this->items = $items;
        $this->page = $page;
        $this->limit = $limit;
        $this->total = $total;
    }

    /**
     * @return DocumentView[]
     */
    public function items(): array
    {
        return $this->items;
    }

    public function page(): int
    {
        return $this->page;
    }

    public function limit(): int
    {
        return $this->limit;
    }

    public function total(): int
    {
        return $this->total;
    }
}
