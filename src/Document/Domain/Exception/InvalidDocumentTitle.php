<?php

declare(strict_types=1);

namespace App\Document\Domain\Exception;

final class InvalidDocumentTitle extends DocumentDomainException
{
    public static function cannotBeEmpty(): self
    {
        return new self('Document title cannot be empty.');
    }
}
