<?php

declare(strict_types=1);

namespace App\Tests\Document\Domain;

use App\Document\Domain\DocumentStatus;
use App\Document\Domain\Exception\InvalidDocumentStatus;
use PHPUnit\Framework\TestCase;

final class DocumentStatusTest extends TestCase
{
    public function testDraftCanTransitionToPublished(): void
    {
        self::assertTrue(DocumentStatus::draft()->canTransitionTo(DocumentStatus::published()));
    }

    public function testDraftCanTransitionToArchived(): void
    {
        self::assertTrue(DocumentStatus::draft()->canTransitionTo(DocumentStatus::archived()));
    }

    public function testPublishedCanTransitionToArchived(): void
    {
        self::assertTrue(DocumentStatus::published()->canTransitionTo(DocumentStatus::archived()));
    }

    public function testPublishedCannotTransitionBackToDraft(): void
    {
        self::assertFalse(DocumentStatus::published()->canTransitionTo(DocumentStatus::draft()));
    }

    public function testArchivedIsATerminalState(): void
    {
        self::assertFalse(DocumentStatus::archived()->canTransitionTo(DocumentStatus::draft()));
        self::assertFalse(DocumentStatus::archived()->canTransitionTo(DocumentStatus::published()));
    }

    public function testFromStringAcceptsKnownValues(): void
    {
        self::assertTrue(DocumentStatus::fromString('draft')->equals(DocumentStatus::draft()));
        self::assertTrue(DocumentStatus::fromString('published')->equals(DocumentStatus::published()));
        self::assertTrue(DocumentStatus::fromString('archived')->equals(DocumentStatus::archived()));
    }

    public function testFromStringRejectsUnknownValue(): void
    {
        $this->expectException(InvalidDocumentStatus::class);

        DocumentStatus::fromString('unknown');
    }
}
