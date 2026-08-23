<?php

declare(strict_types=1);

namespace App\Tests\Document;

use App\Document\Domain\Document;
use App\Document\Domain\DocumentId;
use App\Document\Domain\DocumentRepositoryInterface;
use App\Document\Domain\Exception\DocumentNotFound;

/**
 * A fake, not a mock: it holds real Document objects in an array instead
 * of talking to PostgreSQL. Application tests depend only on
 * DocumentRepositoryInterface, so this can stand in for the future
 * Doctrine implementation without the test suite knowing the difference
 * — that substitutability is exactly what the Dependency Inversion
 * Principle buys us. It stays under tests/ rather than src/ because
 * nothing in production ever needs it.
 */
final class InMemoryDocumentRepository implements DocumentRepositoryInterface
{
    /** @var array<string, Document> */
    private array $documents = [];

    public function save(Document $document): void
    {
        $this->documents[(string) $document->id()] = $document;
    }

    public function get(DocumentId $id): Document
    {
        $document = $this->documents[(string) $id] ?? null;

        if ($document === null) {
            throw DocumentNotFound::withId($id);
        }

        return $document;
    }

    public function remove(Document $document): void
    {
        unset($this->documents[(string) $document->id()]);
    }

    public function count(): int
    {
        return count($this->documents);
    }
}
