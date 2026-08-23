<?php

declare(strict_types=1);

namespace App\Tests\Document\Infrastructure\Persistence\Doctrine;

use App\Document\Domain\Document;
use App\Document\Domain\DocumentId;
use App\Document\Domain\DocumentRepositoryInterface;
use App\Document\Domain\Exception\DocumentNotFound;
use App\Document\Infrastructure\Persistence\Doctrine\DoctrineDocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Real Doctrine, real PostgreSQL test database (searchhub_test) — no
 * mocked EntityManager. This proves the actual round trip:
 * Domain Document -> Doctrine -> PostgreSQL -> Doctrine hydration ->
 * Domain Document, including the DocumentId/DocumentStatus/tags/
 * DateTimeImmutable conversions the custom Types are responsible for.
 *
 * Each test runs inside its own transaction, rolled back in tearDown(),
 * so tests never leave data behind for one another.
 */
final class DoctrineDocumentRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private DocumentRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();

        self::bootKernel();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->getConnection()->beginTransaction();

        $repository = self::getContainer()->get(DocumentRepositoryInterface::class);
        self::assertInstanceOf(DoctrineDocumentRepository::class, $repository);

        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    protected function tearDown(): void
    {
        $connection = $this->entityManager->getConnection();
        if ($connection->isTransactionActive()) {
            $connection->rollBack();
        }
        $this->entityManager->close();

        parent::tearDown();
    }

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

    public function testSaveAndGetRoundTripsAllFields(): void
    {
        $document = $this->createDocument();

        $this->repository->save($document);
        $this->entityManager->clear();

        $reloaded = $this->repository->get($document->id());

        self::assertTrue($document->id()->equals($reloaded->id()));
        self::assertSame($document->title(), $reloaded->title());
        self::assertSame($document->content(), $reloaded->content());
        self::assertSame($document->type(), $reloaded->type());
        self::assertSame($document->tags(), $reloaded->tags());
        self::assertTrue($reloaded->status()->isDraft());

        // Postgres stores TIMESTAMP(0) (Doctrine's default datetime_immutable
        // mapping) — whole-second precision, so microseconds don't survive
        // the round trip. Comparing at second granularity is the honest
        // assertion here, not full object equality.
        self::assertSame(
            $document->createdAt()->format('Y-m-d H:i:s'),
            $reloaded->createdAt()->format('Y-m-d H:i:s')
        );
        self::assertSame(
            $document->updatedAt()->format('Y-m-d H:i:s'),
            $reloaded->updatedAt()->format('Y-m-d H:i:s')
        );
    }

    public function testUpdatingThroughDomainBehaviorPersistsOnFlush(): void
    {
        $document = $this->createDocument();
        $this->repository->save($document);

        $document->publish();
        $document->changeTitle('Understanding DDD (updated)');
        $this->repository->save($document);
        $this->entityManager->clear();

        $reloaded = $this->repository->get($document->id());

        self::assertTrue($reloaded->status()->isPublished());
        self::assertSame('Understanding DDD (updated)', $reloaded->title());
    }

    public function testRemoveDeletesTheDocument(): void
    {
        $document = $this->createDocument();
        $this->repository->save($document);

        $this->repository->remove($document);
        $this->entityManager->clear();

        $this->expectException(DocumentNotFound::class);

        $this->repository->get($document->id());
    }

    public function testGetOnUnknownIdThrowsDocumentNotFound(): void
    {
        $this->expectException(DocumentNotFound::class);

        $this->repository->get(DocumentId::generate());
    }
}
