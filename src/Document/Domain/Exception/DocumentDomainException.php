<?php

declare(strict_types=1);

namespace App\Document\Domain\Exception;

/**
 * Base type for every business rule violation raised by the Document domain.
 * Application code can catch this single type when it only cares that
 * "a document rule was violated", or catch a specific subclass when it
 * needs to react differently (e.g. map to a distinct API error code).
 */
abstract class DocumentDomainException extends \DomainException
{
}
