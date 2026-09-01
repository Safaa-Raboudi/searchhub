<?php

declare(strict_types=1);

namespace App\Document\Application\Get;

use App\Document\Domain\DocumentId;

final class GetDocumentQuery
{
    private DocumentId $id;

    public function __construct(DocumentId $id)
    {
        $this->id = $id;
    }

    public function id(): DocumentId
    {
        return $this->id;
    }
}
