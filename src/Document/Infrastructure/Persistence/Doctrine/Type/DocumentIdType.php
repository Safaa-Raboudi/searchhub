<?php

declare(strict_types=1);

namespace App\Document\Infrastructure\Persistence\Doctrine\Type;

use App\Document\Domain\DocumentId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

/**
 * Converts between the Domain's DocumentId value object and a native
 * Postgres UUID column. Document's `id` property is typed DocumentId, not
 * string, so without this the ORM would have nothing capable of turning a
 * raw DB string back into that object (DocumentId's constructor is
 * private) or the object back into a bindable SQL value.
 */
final class DocumentIdType extends Type
{
    public const NAME = 'document_id';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getGuidTypeDeclarationSQL($column);
    }

    /**
     * @param mixed $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DocumentId) {
            return (string) $value;
        }

        throw ConversionException::conversionFailedInvalidType($value, self::NAME, ['null', DocumentId::class]);
    }

    /**
     * @param mixed $value
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?DocumentId
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof DocumentId) {
            return $value;
        }

        try {
            return DocumentId::fromString($value);
        } catch (\InvalidArgumentException $e) {
            throw ConversionException::conversionFailed($value, self::NAME, $e);
        }
    }

    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * Without this, Postgres schema introspection sees a plain `uuid`
     * column and reverse-maps it to DBAL's built-in guid type, not this
     * one — doctrine:schema:validate would then report a false "type
     * changed" diff against the ORM metadata forever. The SQL comment
     * this adds (`(DC2Type:document_id)`) is how Doctrine tells the two
     * apart on introspection, the same mechanism it already uses for
     * datetime_immutable.
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
