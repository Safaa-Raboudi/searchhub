<?php

declare(strict_types=1);

namespace App\Tests\Document\Domain;

use App\Document\Domain\DocumentId;
use PHPUnit\Framework\TestCase;

final class DocumentIdTest extends TestCase
{
    public function testGeneratedIdIsAValidUuid(): void
    {
        $id = DocumentId::generate();

        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            (string) $id
        );
    }

    public function testTwoGeneratedIdsDiffer(): void
    {
        $first = DocumentId::generate();
        $second = DocumentId::generate();

        self::assertFalse($first->equals($second));
    }

    public function testCanBeReconstructedFromString(): void
    {
        $original = DocumentId::generate();

        $reconstructed = DocumentId::fromString((string) $original);

        self::assertTrue($original->equals($reconstructed));
        self::assertSame((string) $original, (string) $reconstructed);
    }

    public function testInvalidStringCannotBeUsedAsAnId(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        DocumentId::fromString('not-a-uuid');
    }
}
