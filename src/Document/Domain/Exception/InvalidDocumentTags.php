<?php

declare(strict_types=1);

namespace App\Document\Domain\Exception;

final class InvalidDocumentTags extends DocumentDomainException
{
    public static function cannotContainEmptyValue(): self
    {
        return new self('Document tags cannot contain an empty value.');
    }
}
