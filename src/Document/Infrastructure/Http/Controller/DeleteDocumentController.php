<?php

declare(strict_types=1);

namespace App\Document\Infrastructure\Http\Controller;

use App\Document\Application\Delete\DeleteDocumentCommand;
use App\Document\Application\Delete\DeleteDocumentHandler;
use App\Document\Domain\DocumentId;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
final class DeleteDocumentController
{
    private const UUID_PATTERN = '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}';

    private DeleteDocumentHandler $handler;

    public function __construct(DeleteDocumentHandler $handler)
    {
        $this->handler = $handler;
    }

    #[Route(
        '/api/documents/{id}',
        name: 'api_document_delete',
        methods: ['DELETE'],
        requirements: ['id' => self::UUID_PATTERN]
    )]
    public function __invoke(string $id): Response
    {
        ($this->handler)(new DeleteDocumentCommand(DocumentId::fromString($id)));

        // No body on a 204 — a JSON payload here would contradict the
        // status code's own meaning ("no content").
        return new Response(null, 204);
    }
}
