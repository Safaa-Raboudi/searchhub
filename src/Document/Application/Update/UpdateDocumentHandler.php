<?php

declare(strict_types=1);

namespace App\Document\Application\Update;

use App\Document\Application\DocumentView;
use App\Document\Domain\DocumentRepositoryInterface;

/**
 * Only calls the Domain behavior method for a field that was actually
 * provided — this is the entire PATCH semantics: no business rule about
 * what counts as "unchanged" lives here, just "was a value given".
 * Validity of a given value (e.g. non-empty title) is still enforced by
 * Document itself (changeTitle(), etc.), not re-checked here.
 */
final class UpdateDocumentHandler
{
    private DocumentRepositoryInterface $repository;

    public function __construct(DocumentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(UpdateDocumentCommand $command): DocumentView
    {
        $document = $this->repository->get($command->id());

        if ($command->title() !== null) {
            $document->changeTitle($command->title());
        }

        if ($command->content() !== null) {
            $document->changeContent($command->content());
        }

        if ($command->type() !== null) {
            $document->changeType($command->type());
        }

        if ($command->tags() !== null) {
            $document->replaceTags($command->tags());
        }

        $this->repository->save($document);

        return DocumentView::fromDocument($document);
    }
}
