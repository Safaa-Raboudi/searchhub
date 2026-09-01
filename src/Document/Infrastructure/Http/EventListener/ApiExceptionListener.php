<?php

declare(strict_types=1);

namespace App\Document\Infrastructure\Http\EventListener;

use App\Document\Domain\Exception\DocumentDomainException;
use App\Document\Domain\Exception\DocumentNotFound;
use App\Document\Infrastructure\Http\Exception\ValidationFailedException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Translates exceptions into a consistent JSON error envelope for the
 * `/api` surface, so no controller repeats a try/catch to do this itself.
 *
 * Scoped to Document exceptions for now, plus the framework-level
 * concerns (malformed JSON, routing errors, unexpected failures) that
 * aren't module-specific anyway. With only one bounded context in the
 * project so far, generalizing this into a Shared module behind marker
 * interfaces would be speculative — worth doing the moment a second
 * module needs the same translation, not before.
 *
 * 400 vs 422: 400 means the request couldn't even be parsed (malformed
 * JSON) — a protocol-level problem. 422 means the request WAS well-formed
 * and understood, but its content breaks a validation rule or a Domain
 * invariant — a content-level problem. Both HTTP validation failures and
 * Domain rule violations are content-level, so both map to 422.
 */
final class ApiExceptionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!str_starts_with($event->getRequest()->getPathInfo(), '/api/')) {
            return;
        }

        $exception = $event->getThrowable();

        $event->setResponse($this->toResponse($exception));
    }

    private function toResponse(\Throwable $exception): JsonResponse
    {
        if ($exception instanceof \JsonException) {
            return $this->errorResponse(400, 'MALFORMED_JSON', 'The request body is not valid JSON.');
        }

        if ($exception instanceof ValidationFailedException) {
            return $this->errorResponse(422, 'VALIDATION_FAILED', $exception->getMessage(), $exception->violations());
        }

        if ($exception instanceof DocumentNotFound) {
            return $this->errorResponse(404, 'DOCUMENT_NOT_FOUND', $exception->getMessage());
        }

        if ($exception instanceof DocumentDomainException) {
            return $this->errorResponse(422, 'DOMAIN_RULE_VIOLATION', $exception->getMessage());
        }

        if ($exception instanceof HttpExceptionInterface) {
            return $this->errorResponse(
                $exception->getStatusCode(),
                'HTTP_' . $exception->getStatusCode(),
                $exception->getMessage(),
                null,
                $exception->getHeaders()
            );
        }

        return $this->errorResponse(500, 'INTERNAL_ERROR', 'An unexpected error occurred.');
    }

    /**
     * @param array<string, string[]>|null $violations
     * @param array<string, string> $headers
     */
    private function errorResponse(
        int $status,
        string $code,
        string $message,
        ?array $violations = null,
        array $headers = []
    ): JsonResponse {
        $error = [
            'code' => $code,
            'message' => $message,
        ];

        if ($violations !== null) {
            $error['violations'] = $violations;
        }

        return new JsonResponse(['error' => $error], $status, $headers);
    }
}
