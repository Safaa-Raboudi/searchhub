<?php

declare(strict_types=1);

namespace App\Document\Domain;

use App\Document\Domain\Exception\DocumentNotFound;

/**
 * Defined in the Domain layer, implemented in Infrastructure (Dependency
 * Inversion Principle): the Domain and Application layers depend on this
 * abstraction, never on Doctrine directly. Only the operations the domain
 * currently needs are declared here — search, filtering and pagination
 * belong to the Search module's own abstraction, not this repository.
 */
interface DocumentRepositoryInterface
{
    public function save(Document $document): void;

    /**
     * @throws DocumentNotFound
     */
    public function get(DocumentId $id): Document;

    public function remove(Document $document): void;
}
