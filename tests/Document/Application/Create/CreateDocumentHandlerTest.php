<?php

declare(strict_types=1);

namespace App\Tests\Document\Application\Create;

use App\Document\Application\Create\CreateDocumentCommand;
use App\Document\Application\Create\CreateDocumentHandler;
use App\Document\Domain\Exception\InvalidDocumentContent;
use App\Document\Domain\Exception\InvalidDocumentTitle;
use App\Document\Domain\Exception\InvalidDocumentType;
use App\Tests\Document\InMemoryDocumentRepository;
use PHPUnit\Framework\TestCase;

final class CreateDocumentHandlerTest extends TestCase
{
    public function testHandlerSavesANewDraftDocument(): void
    {
        $repository = new InMemoryDocumentRepository();
        $handler = new CreateDocumentHandler($repository);

        $result = $handler(new CreateDocumentCommand(
            'Understanding DDD',
            'Domain-Driven Design is about modeling the business.',
            'article',
            ['ddd', 'architecture']
        ));

        self::assertSame(1, $repository->count());

        $saved = $repository->get($result->id());
        self::assertSame('Understanding DDD', $saved->title());
        self::assertSame('Domain-Driven Design is about modeling the business.', $saved->content());
        self::assertSame('article', $saved->type());
        self::assertSame(['ddd', 'architecture'], $saved->tags());
        self::assertTrue($saved->status()->isDraft());
    }

    public function testResultReflectsThePersistedDocument(): void
    {
        $repository = new InMemoryDocumentRepository();
        $handler = new CreateDocumentHandler($repository);

        $result = $handler(new CreateDocumentCommand('Title', 'Content', 'note'));

        $saved = $repository->get($result->id());
        self::assertTrue($result->status()->isDraft());
        self::assertEquals($saved->createdAt(), $result->createdAt());
    }

    public function testEachCreationGeneratesADistinctId(): void
    {
        $repository = new InMemoryDocumentRepository();
        $handler = new CreateDocumentHandler($repository);

        $first = $handler(new CreateDocumentCommand('Title 1', 'Content 1', 'note'));
        $second = $handler(new CreateDocumentCommand('Title 2', 'Content 2', 'note'));

        self::assertFalse($first->id()->equals($second->id()));
        self::assertSame(2, $repository->count());
    }

    public function testEmptyTitleIsRejectedByTheDomainAndNothingIsSaved(): void
    {
        $repository = new InMemoryDocumentRepository();
        $handler = new CreateDocumentHandler($repository);

        $this->expectException(InvalidDocumentTitle::class);

        try {
            $handler(new CreateDocumentCommand('   ', 'Content', 'note'));
        } finally {
            self::assertSame(0, $repository->count());
        }
    }

    public function testEmptyContentIsRejectedByTheDomainAndNothingIsSaved(): void
    {
        $repository = new InMemoryDocumentRepository();
        $handler = new CreateDocumentHandler($repository);

        $this->expectException(InvalidDocumentContent::class);

        try {
            $handler(new CreateDocumentCommand('Title', '   ', 'note'));
        } finally {
            self::assertSame(0, $repository->count());
        }
    }

    public function testEmptyTypeIsRejectedByTheDomainAndNothingIsSaved(): void
    {
        $repository = new InMemoryDocumentRepository();
        $handler = new CreateDocumentHandler($repository);

        $this->expectException(InvalidDocumentType::class);

        try {
            $handler(new CreateDocumentCommand('Title', 'Content', '   '));
        } finally {
            self::assertSame(0, $repository->count());
        }
    }
}
