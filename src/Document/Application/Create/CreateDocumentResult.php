<?php

declare(strict_types=1);

namespace App\Document\Application\Create;

use App\Document\Domain\DocumentId;
use App\Document\Domain\DocumentStatus;

/**
 * Deliberately narrower than Document: it exposes only the information
 * that is new after creation (the generated id, initial status, creation
 * time) rather than the whole aggregate. Returning Document itself would
 * let a caller — a controller, a future message handler — reach for
 * publish()/changeTitle()/etc. directly instead of going through their
 * own use cases, which defeats the point of having use cases.
 *
 * This is still an Application-layer type, not an HTTP response: it's
 * fine for it to expose Domain value objects (DocumentId, DocumentStatus),
 * because Application is allowed to depend on Domain. The future API
 * layer will build its own response DTO from this result, converting
 * these into JSON-safe scalars, adding links, envelopes, etc. Collapsing
 * the two concerns into one class would couple the use case's return
 * shape to a specific HTTP representation.
 */
final class CreateDocumentResult
{
    private DocumentId $id;
    private DocumentStatus $status;
    private \DateTimeImmutable $createdAt;

    public function __construct(DocumentId $id, DocumentStatus $status, \DateTimeImmutable $createdAt)
    {
        $this->id = $id;
        $this->status = $status;
        $this->createdAt = $createdAt;
    }

    public function id(): DocumentId
    {
        return $this->id;
    }

    public function status(): DocumentStatus
    {
        return $this->status;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
