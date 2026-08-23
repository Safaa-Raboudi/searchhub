<?php

declare(strict_types=1);

namespace App\Tests\Document\Domain;

use App\Document\Domain\Document;
use App\Document\Domain\DocumentId;
use App\Document\Domain\Exception\InvalidDocumentContent;
use App\Document\Domain\Exception\InvalidDocumentStatusTransition;
use App\Document\Domain\Exception\InvalidDocumentTags;
use App\Document\Domain\Exception\InvalidDocumentTitle;
use App\Document\Domain\Exception\InvalidDocumentType;
use PHPUnit\Framework\TestCase;

final class DocumentTest extends TestCase
{
    private function createDocument(): Document
    {
        return Document::create(
            DocumentId::generate(),
            'Understanding DDD',
            'Domain-Driven Design is about modeling the business.',
            'article',
            ['ddd', 'architecture']
        );
    }

    public function testValidDocumentCanBeCreated(): void
    {
        $document = $this->createDocument();

        self::assertSame('Understanding DDD', $document->title());
        self::assertSame('Domain-Driven Design is about modeling the business.', $document->content());
        self::assertSame('article', $document->type());
        self::assertSame(['ddd', 'architecture'], $document->tags());
    }

    public function testNewDocumentDefaultsToDraftStatus(): void
    {
        $document = $this->createDocument();

        self::assertTrue($document->status()->isDraft());
    }

    public function testNewDocumentHasCreatedAndUpdatedTimestamps(): void
    {
        $before = new \DateTimeImmutable();
        $document = $this->createDocument();
        $after = new \DateTimeImmutable();

        self::assertGreaterThanOrEqual($before, $document->createdAt());
        self::assertLessThanOrEqual($after, $document->createdAt());
        self::assertEquals($document->createdAt(), $document->updatedAt());
    }

    public function testEmptyTitleIsRejected(): void
    {
        $this->expectException(InvalidDocumentTitle::class);

        Document::create(DocumentId::generate(), '   ', 'content', 'article');
    }

    public function testEmptyContentIsRejected(): void
    {
        $this->expectException(InvalidDocumentContent::class);

        Document::create(DocumentId::generate(), 'title', '   ', 'article');
    }

    public function testEmptyTypeIsRejected(): void
    {
        $this->expectException(InvalidDocumentType::class);

        Document::create(DocumentId::generate(), 'title', 'content', '   ');
    }

    public function testTagsCannotContainAnEmptyValue(): void
    {
        $this->expectException(InvalidDocumentTags::class);

        Document::create(DocumentId::generate(), 'title', 'content', 'article', ['valid', '  ']);
    }

    public function testTitleCanBeChanged(): void
    {
        $document = $this->createDocument();
        $previousUpdatedAt = $document->updatedAt();

        $document->changeTitle('A New Title');

        self::assertSame('A New Title', $document->title());
        self::assertGreaterThanOrEqual($previousUpdatedAt, $document->updatedAt());
    }

    public function testChangingTitleDoesNotAffectCreatedAt(): void
    {
        $document = $this->createDocument();
        $createdAt = $document->createdAt();

        $document->changeTitle('A New Title');

        self::assertEquals($createdAt, $document->createdAt());
    }

    public function testContentCanBeChanged(): void
    {
        $document = $this->createDocument();

        $document->changeContent('Updated content.');

        self::assertSame('Updated content.', $document->content());
    }

    public function testTypeCanBeChanged(): void
    {
        $document = $this->createDocument();

        $document->changeType('tutorial');

        self::assertSame('tutorial', $document->type());
    }

    public function testTagsCanBeReplaced(): void
    {
        $document = $this->createDocument();

        $document->replaceTags(['new-tag']);

        self::assertSame(['new-tag'], $document->tags());
    }

    public function testDraftDocumentCanBePublished(): void
    {
        $document = $this->createDocument();

        $document->publish();

        self::assertTrue($document->status()->isPublished());
    }

    public function testPublishedDocumentCanBeArchived(): void
    {
        $document = $this->createDocument();
        $document->publish();

        $document->archive();

        self::assertTrue($document->status()->isArchived());
    }

    public function testDraftDocumentCanBeArchivedDirectly(): void
    {
        $document = $this->createDocument();

        $document->archive();

        self::assertTrue($document->status()->isArchived());
    }

    public function testArchivedDocumentCannotBePublished(): void
    {
        $document = $this->createDocument();
        $document->archive();

        $this->expectException(InvalidDocumentStatusTransition::class);

        $document->publish();
    }

    public function testPublishedDocumentCannotBePublishedAgain(): void
    {
        $document = $this->createDocument();
        $document->publish();

        $this->expectException(InvalidDocumentStatusTransition::class);

        $document->publish();
    }

    public function testArchivedDocumentCannotBeArchivedAgain(): void
    {
        $document = $this->createDocument();
        $document->archive();

        $this->expectException(InvalidDocumentStatusTransition::class);

        $document->archive();
    }
}
