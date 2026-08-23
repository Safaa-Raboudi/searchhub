<?php

declare(strict_types=1);

namespace App\Document\Domain;

use App\Document\Domain\Exception\InvalidDocumentStatus;

/**
 * PHP 8.0 has no native enums, so this is a hand-rolled "enum-like" value
 * object: a private constructor plus named static constructors, comparing
 * by an internal string value instead of object identity. This is the
 * standard pre-8.1 pattern for a closed set of values. Once the project
 * migrates to PHP 8.1+, this is a natural candidate to become a real
 * `enum DocumentStatus: string`, backed cases and all — a good example of
 * a language-driven modernization Rector can help with later.
 */
final class DocumentStatus
{
    private const DRAFT = 'draft';
    private const PUBLISHED = 'published';
    private const ARCHIVED = 'archived';

    private const VALID_VALUES = [self::DRAFT, self::PUBLISHED, self::ARCHIVED];

    /** @var array<string, string[]> */
    private const ALLOWED_TRANSITIONS = [
        self::DRAFT => [self::PUBLISHED, self::ARCHIVED],
        self::PUBLISHED => [self::ARCHIVED],
        self::ARCHIVED => [],
    ];

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function draft(): self
    {
        return new self(self::DRAFT);
    }

    public static function published(): self
    {
        return new self(self::PUBLISHED);
    }

    public static function archived(): self
    {
        return new self(self::ARCHIVED);
    }

    public static function fromString(string $value): self
    {
        if (!in_array($value, self::VALID_VALUES, true)) {
            throw InvalidDocumentStatus::forValue($value);
        }

        return new self($value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function isDraft(): bool
    {
        return $this->value === self::DRAFT;
    }

    public function isPublished(): bool
    {
        return $this->value === self::PUBLISHED;
    }

    public function isArchived(): bool
    {
        return $this->value === self::ARCHIVED;
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target->value, self::ALLOWED_TRANSITIONS[$this->value], true);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
