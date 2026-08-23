<?php

declare(strict_types=1);

namespace App\Document\Domain\Exception;

use App\Document\Domain\DocumentId;

final class DocumentNotFound extends DocumentDomainException
{
    public static function withId(DocumentId $id): self
    {
        return new self(sprintf('Document with id "%s" was not found.', $id));
    }
}
