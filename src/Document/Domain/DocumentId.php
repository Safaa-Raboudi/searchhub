<?php

declare(strict_types=1);

namespace App\Document\Domain;

use Symfony\Component\Uid\Uuid;

/**
 * Wraps Symfony's Uuid instead of exposing it directly, so the Document
 * domain has its own identifier type and isn't coupled everywhere to a
 * specific UUID library's API. Swapping the underlying implementation
 * later would only touch this class.
 */
final class DocumentId
{
    private Uuid $uuid;

    private function __construct(Uuid $uuid)
    {
        $this->uuid = $uuid;
    }

    public static function generate(): self
    {
        return new self(Uuid::v4());
    }

    public static function fromString(string $id): self
    {
        return new self(Uuid::fromString($id));
    }

    public function equals(self $other): bool
    {
        return $this->uuid->equals($other->uuid);
    }

    public function __toString(): string
    {
        return $this->uuid->toRfc4122();
    }
}
