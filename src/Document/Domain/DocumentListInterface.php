<?php

declare(strict_types=1);

namespace App\Document\Domain;

/**
 * Deliberately separate from DocumentRepositoryInterface (Interface
 * Segregation Principle): listing is a collection-oriented read concern,
 * not a per-aggregate persistence operation. A handler that only needs
 * to list documents depends on this alone and is never handed the power
 * to save/remove — it can't, its constructor never receives a
 * DocumentRepositoryInterface. The same Infrastructure class may
 * implement both interfaces without them being the same abstraction.
 *
 * This is not the future Search API: no filtering, sorting, or text
 * search — just enough to page through everything currently stored.
 */
interface DocumentListInterface
{
    /**
     * @return Document[]
     */
    public function paginate(int $page, int $limit): array;

    public function count(): int;
}
