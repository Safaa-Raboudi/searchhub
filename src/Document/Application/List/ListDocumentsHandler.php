<?php

declare(strict_types=1);

namespace App\Document\Application\List;

use App\Document\Application\DocumentView;
use App\Document\Domain\DocumentListInterface;

final class ListDocumentsHandler
{
    private DocumentListInterface $documents;

    public function __construct(DocumentListInterface $documents)
    {
        $this->documents = $documents;
    }

    public function __invoke(ListDocumentsQuery $query): ListDocumentsResult
    {
        $documents = $this->documents->paginate($query->page(), $query->limit());
        $total = $this->documents->count();

        $views = array_map(
            static fn ($document) => DocumentView::fromDocument($document),
            $documents
        );

        return new ListDocumentsResult($views, $query->page(), $query->limit(), $total);
    }
}
