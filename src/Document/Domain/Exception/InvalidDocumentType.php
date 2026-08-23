<?php

declare(strict_types=1);

namespace App\Document\Domain\Exception;

final class InvalidDocumentType extends DocumentDomainException
{
    public static function cannotBeEmpty(): self
    {
        return new self('Document type cannot be empty.');
    }
}
