<?php

declare(strict_types=1);

namespace App\Document\Application;

use App\Document\Domain\Document;
use App\Document\Domain\DocumentId;
use App\Document\Domain\DocumentStatus;

/**
 * The shared read-model shape returned by every Document use case
 * (Create/Get/Update/List): the full current state of a document, for
 * callers that only need to display it, not mutate it. Introduced in
 * Phase 4 to replace Phase 2's narrower CreateDocumentResult (which only
 * exposed id/status/createdAt) once multiple use cases needed the exact
 * same full shape — one shared view is simpler than four overlapping
 * result classes.
 *
 * Still an Application-layer type, not an HTTP response: it exposes
 * Domain value objects (DocumentId, DocumentStatus), not JSON-safe
 * scalars. The HTTP layer maps this to its own response representation.
 */
final class DocumentView
{
    private DocumentId $id;
    private string $title;
    private string $content;
    private string $type;

    /** @var string[] */
    private array $tags;

    private DocumentStatus $status;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    /**
     * @param string[] $tags
     */
    public function __construct(
        DocumentId $id,
        string $title,
        string $content,
        string $type,
        array $tags,
        DocumentStatus $status,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->type = $type;
        $this->tags = $tags;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function fromDocument(Document $document): self
    {
        return new self(
            $document->id(),
            $document->title(),
            $document->content(),
            $document->type(),
            $document->tags(),
            $document->status(),
            $document->createdAt(),
            $document->updatedAt()
        );
    }

    public function id(): DocumentId
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function content(): string
    {
        return $this->content;
    }

    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return string[]
     */
    public function tags(): array
    {
        return $this->tags;
    }

    public function status(): DocumentStatus
    {
        return $this->status;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
