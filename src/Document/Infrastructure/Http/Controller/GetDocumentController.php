<?php

declare(strict_types=1);

namespace App\Document\Infrastructure\Http\Controller;

use App\Document\Application\Get\GetDocumentHandler;
use App\Document\Application\Get\GetDocumentQuery;
use App\Document\Domain\DocumentId;
use App\Document\Infrastructure\Http\Response\DocumentResponseFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
final class GetDocumentController
{
    /**
     * A path segment that doesn't look like a UUID can never correspond
     * to a stored document, so the router rejects it before the
     * controller runs at all — it falls through to Symfony's normal
     * "no route found" (404), the same status a genuinely missing
     * document gets.
     */
    private const UUID_PATTERN = '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}';

    private GetDocumentHandler $handler;

    public function __construct(GetDocumentHandler $handler)
    {
        $this->handler = $handler;
    }

    #[Route(
        '/api/documents/{id}',
        name: 'api_document_get',
        methods: ['GET'],
        requirements: ['id' => self::UUID_PATTERN]
    )]
    public function __invoke(string $id): JsonResponse
    {
        $view = ($this->handler)(new GetDocumentQuery(DocumentId::fromString($id)));

        return new JsonResponse(DocumentResponseFactory::fromView($view));
    }
}
