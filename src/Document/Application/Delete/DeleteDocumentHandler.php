<?php

declare(strict_types=1);

namespace App\Document\Application\Delete;

use App\Document\Domain\DocumentRepositoryInterface;

final class DeleteDocumentHandler
{
    private DocumentRepositoryInterface $repository;

    public function __construct(DocumentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(DeleteDocumentCommand $command): void
    {
        $document = $this->repository->get($command->id());

        $this->repository->remove($document);
    }
}
