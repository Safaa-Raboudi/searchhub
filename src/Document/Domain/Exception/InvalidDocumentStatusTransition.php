<?php

declare(strict_types=1);

namespace App\Document\Domain\Exception;

use App\Document\Domain\DocumentStatus;

final class InvalidDocumentStatusTransition extends DocumentDomainException
{
    public static function fromTo(DocumentStatus $from, DocumentStatus $to): self
    {
        return new self(sprintf(
            'Cannot transition document status from "%s" to "%s".',
            $from,
            $to
        ));
    }
}
