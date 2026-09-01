<?php

declare(strict_types=1);

namespace App\Document\Infrastructure\Http\Controller;

use App\Document\Application\List\ListDocumentsHandler;
use App\Document\Application\List\ListDocumentsQuery;
use App\Document\Infrastructure\Http\Request\ListDocumentsRequest;
use App\Document\Infrastructure\Http\Response\DocumentResponseFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Lists documents from PostgreSQL — not the future Search API. GET
 * /api/search (OpenSearch-backed, full-text, filters) is a separate,
 * later concern; this endpoint only paginates through everything stored.
 */
#[AsController]
final class ListDocumentsController
{
    private ListDocumentsHandler $handler;

    public function __construct(ListDocumentsHandler $handler)
    {
        $this->handler = $handler;
    }

    #[Route('/api/documents', name: 'api_document_list', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $dto = ListDocumentsRequest::fromQuery($request->query->all());

        $result = ($this->handler)(new ListDocumentsQuery($dto->page(), $dto->limit()));

        return new JsonResponse(DocumentResponseFactory::fromListResult($result));
    }
}
