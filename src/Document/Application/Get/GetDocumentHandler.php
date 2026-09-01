<?php

declare(strict_types=1);

namespace App\Document\Application\Get;

use App\Document\Application\DocumentView;
use App\Document\Domain\DocumentRepositoryInterface;
use App\Document\Domain\Exception\DocumentNotFound;

/**
 * @see DocumentNotFound thrown directly by DocumentRepositoryInterface::get()
 * when no document exists for the given id; this handler doesn't catch it,
 * it propagates to the HTTP layer for translation into a 404.
 */
final class GetDocumentHandler
{
    private DocumentRepositoryInterface $repository;

    public function __construct(DocumentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(GetDocumentQuery $query): DocumentView
    {
        $document = $this->repository->get($query->id());

        return DocumentView::fromDocument($document);
    }
}
