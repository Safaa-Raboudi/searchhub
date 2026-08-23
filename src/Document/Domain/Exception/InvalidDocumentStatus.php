<?php

declare(strict_types=1);

namespace App\Document\Domain\Exception;

final class InvalidDocumentStatus extends DocumentDomainException
{
    public static function forValue(string $value): self
    {
        return new self(sprintf('"%s" is not a valid document status.', $value));
    }
}
