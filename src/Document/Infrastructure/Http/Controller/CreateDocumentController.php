<?php

declare(strict_types=1);

namespace App\Document\Infrastructure\Http\Controller;

use App\Document\Application\Create\CreateDocumentHandler;
use App\Document\Infrastructure\Http\Exception\ValidationFailedException;
use App\Document\Infrastructure\Http\Request\CreateDocumentRequest;
use App\Document\Infrastructure\Http\Response\DocumentResponseFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Deliberately thin: decode, validate, hand off to the Application
 * handler, map the result to JSON. No business rule is decided here —
 * Document::create() (via CreateDocumentHandler) owns all of that.
 *
 * #[AsController]: without it, this private autowired service can't be
 * resolved as a route's controller (it isn't an AbstractController
 * subclass, the usual way Symfony recognizes one) — this attribute is
 * exactly the mechanism Symfony provides for a plain, invokable
 * controller service, autoconfigured automatically since 5.3.
 */
#[AsController]
final class CreateDocumentController
{
    private CreateDocumentHandler $handler;
    private ValidatorInterface $validator;

    public function __construct(CreateDocumentHandler $handler, ValidatorInterface $validator)
    {
        $this->handler = $handler;
        $this->validator = $validator;
    }

    #[Route('/api/documents', name: 'api_document_create', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $dto = CreateDocumentRequest::fromArray(is_array($data) ? $data : []);

        $violations = $this->validator->validate($dto);

        if (count($violations) > 0) {
            throw ValidationFailedException::fromConstraintViolationList($violations);
        }

        $view = ($this->handler)($dto->toCommand());

        $body = DocumentResponseFactory::fromView($view);

        return new JsonResponse($body, 201, ['Location' => '/api/documents/' . $body['id']]);
    }
}
