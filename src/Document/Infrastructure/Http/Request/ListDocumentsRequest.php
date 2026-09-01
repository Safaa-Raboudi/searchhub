<?php

declare(strict_types=1);

namespace App\Document\Infrastructure\Http\Request;

use App\Document\Infrastructure\Http\Exception\ValidationFailedException;

/**
 * Two plain positive integers with a maximum — simple enough that
 * hand-written checks are clearer than reaching for Symfony Validator
 * constraints here (unlike Create/UpdateDocumentRequest, which validate
 * genuinely varied shapes). Invalid values are rejected outright, not
 * silently clamped, so a caller always knows what was actually applied.
 */
final class ListDocumentsRequest
{
    public const DEFAULT_PAGE = 1;
    public const DEFAULT_LIMIT = 20;
    public const MAX_LIMIT = 100;

    private int $page;
    private int $limit;

    private function __construct(int $page, int $limit)
    {
        $this->page = $page;
        $this->limit = $limit;
    }

    /**
     * @param array<string, mixed> $query
     */
    public static function fromQuery(array $query): self
    {
        $violations = [];

        $page = self::parsePositiveInt($query['page'] ?? null, self::DEFAULT_PAGE, 'page', $violations);
        $limit = self::parsePositiveInt($query['limit'] ?? null, self::DEFAULT_LIMIT, 'limit', $violations);

        if ($limit !== null && $limit > self::MAX_LIMIT) {
            $violations['limit'][] = sprintf('limit must not be greater than %d.', self::MAX_LIMIT);
            $limit = null;
        }

        if ($violations !== [] || $page === null || $limit === null) {
            throw ValidationFailedException::fromFieldMessages($violations);
        }

        return new self($page, $limit);
    }

    public function page(): int
    {
        return $this->page;
    }

    public function limit(): int
    {
        return $this->limit;
    }

    /**
     * @param array<string, string[]> $violations
     */
    private static function parsePositiveInt(mixed $raw, int $default, string $field, array &$violations): ?int
    {
        if ($raw === null) {
            return $default;
        }

        if ((!is_string($raw) && !is_int($raw)) || !ctype_digit((string) $raw)) {
            $violations[$field][] = sprintf('%s must be a positive integer.', $field);

            return null;
        }

        $value = (int) $raw;

        if ($value <= 0) {
            $violations[$field][] = sprintf('%s must be greater than 0.', $field);

            return null;
        }

        return $value;
    }
}
