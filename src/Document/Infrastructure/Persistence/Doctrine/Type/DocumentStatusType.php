<?php

declare(strict_types=1);

namespace App\Document\Infrastructure\Persistence\Doctrine\Type;

use App\Document\Domain\DocumentStatus;
use App\Document\Domain\Exception\InvalidDocumentStatus;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

/**
 * Converts between the Domain's DocumentStatus value object and a plain
 * varchar column. Same reasoning as DocumentIdType: DocumentStatus has a
 * private constructor, so the ORM needs an explicit conversion instead of
 * being able to `new` it directly during hydration.
 */
final class DocumentStatusType extends Type
{
    public const NAME = 'document_status';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        $column['length'] = $column['length'] ?? 20;

        return $platform->getStringTypeDeclarationSQL($column);
    }

    /**
     * @param mixed $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DocumentStatus) {
            return (string) $value;
        }

        throw ConversionException::conversionFailedInvalidType($value, self::NAME, ['null', DocumentStatus::class]);
    }

    /**
     * @param mixed $value
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?DocumentStatus
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DocumentStatus) {
            return $value;
        }

        try {
            return DocumentStatus::fromString($value);
        } catch (InvalidDocumentStatus $e) {
            throw ConversionException::conversionFailed($value, self::NAME, $e);
        }
    }

    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * See DocumentIdType::requiresSQLCommentHint() for why this is needed:
     * without it, a plain varchar column reverse-maps to DBAL's built-in
     * string type on introspection, and doctrine:schema:validate reports
     * a permanent false "type changed" diff against the ORM metadata.
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
