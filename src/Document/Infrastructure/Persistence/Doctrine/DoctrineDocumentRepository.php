<?php

declare(strict_types=1);

namespace App\Document\Infrastructure\Persistence\Doctrine;

use App\Document\Domain\Document;
use App\Document\Domain\DocumentId;
use App\Document\Domain\DocumentListInterface;
use App\Document\Domain\DocumentRepositoryInterface;
use App\Document\Domain\Exception\DocumentNotFound;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Persistence-only: no business rules live here, only EntityManager
 * coordination. Each call flushes itself, so one repository call is one
 * transaction/unit — deliberately simple. A use case that needs a
 * PostgreSQL write and a RabbitMQ dispatch to succeed or fail together
 * will need real cross-resource consistency handling; that is a known
 * future problem (Messenger/RabbitMQ phase), not solved here.
 *
 * Implements two separate, focused interfaces rather than one broad one
 * (Interface Segregation): DocumentRepositoryInterface for per-aggregate
 * persistence, DocumentListInterface for collection-oriented reads. One
 * class backs both here since they share the same table/EntityManager,
 * but a caller only ever depends on the interface it actually needs.
 */
final class DoctrineDocumentRepository implements DocumentRepositoryInterface, DocumentListInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function save(Document $document): void
    {
        $this->entityManager->persist($document);
        $this->entityManager->flush();
    }

    public function get(DocumentId $id): Document
    {
        $document = $this->entityManager->find(Document::class, $id);

        if ($document === null) {
            throw DocumentNotFound::withId($id);
        }

        return $document;
    }

    public function remove(Document $document): void
    {
        $this->entityManager->remove($document);
        $this->entityManager->flush();
    }

    /**
     * @return Document[]
     */
    public function paginate(int $page, int $limit): array
    {
        // Explicit ordering isn't a nice-to-have here: without it,
        // Postgres row order across separate queries is unspecified, and
        // pagination built on an unspecified order isn't real pagination.
        return $this->entityManager->getRepository(Document::class)
            ->createQueryBuilder('d')
            ->orderBy('d.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function count(): int
    {
        $total = $this->entityManager->getRepository(Document::class)
            ->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $total;
    }
}
