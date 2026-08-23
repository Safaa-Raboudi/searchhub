<?php

declare(strict_types=1);

namespace App\Document\Domain\Exception;

final class InvalidDocumentContent extends DocumentDomainException
{
    public static function cannotBeEmpty(): self
    {
        return new self('Document content cannot be empty.');
    }
}
