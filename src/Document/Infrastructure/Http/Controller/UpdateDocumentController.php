<?php

declare(strict_types=1);

namespace App\Document\Infrastructure\Http\Controller;

use App\Document\Application\Update\UpdateDocumentHandler;
use App\Document\Domain\DocumentId;
use App\Document\Infrastructure\Http\Request\UpdateDocumentRequest;
use App\Document\Infrastructure\Http\Response\DocumentResponseFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsController]
final class UpdateDocumentController
{
    private const UUID_PATTERN = '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}';

    private UpdateDocumentHandler $handler;
    private ValidatorInterface $validator;

    public function __construct(UpdateDocumentHandler $handler, ValidatorInterface $validator)
    {
        $this->handler = $handler;
        $this->validator = $validator;
    }

    #[Route(
        '/api/documents/{id}',
        name: 'api_document_update',
        methods: ['PATCH'],
        requirements: ['id' => self::UUID_PATTERN]
    )]
    public function __invoke(Request $request, string $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $dto = UpdateDocumentRequest::fromArray(is_array($data) ? $data : []);

        $command = $dto->toCommand(DocumentId::fromString($id), $this->validator);

        $view = ($this->handler)($command);

        return new JsonResponse(DocumentResponseFactory::fromView($view));
    }
}
