<?php

declare(strict_types=1);

namespace App\Document\Infrastructure\Http\Response;

use App\Document\Application\DocumentView;
use App\Document\Application\List\ListDocumentsResult;

/**
 * Explicit, hand-written mapping from Application-layer types to
 * JSON-safe arrays — no Symfony Serializer, no serialization groups.
 * DocumentView exposes DocumentId/DocumentStatus/DateTimeImmutable
 * objects on purpose (see DocumentView's docblock); this is the one
 * place that turns them into plain strings for the wire, so neither the
 * Domain nor the Application layer needs to know JSON exists.
 */
final class DocumentResponseFactory
{
    /**
     * @return array<string, mixed>
     */
    public static function fromView(DocumentView $view): array
    {
        return [
            'id' => (string) $view->id(),
            'title' => $view->title(),
            'content' => $view->content(),
            'type' => $view->type(),
            'tags' => $view->tags(),
            'status' => (string) $view->status(),
            'createdAt' => $view->createdAt()->format(DATE_ATOM),
            'updatedAt' => $view->updatedAt()->format(DATE_ATOM),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function fromListResult(ListDocumentsResult $result): array
    {
        $limit = $result->limit();
        $total = $result->total();

        return [
            'data' => array_map([self::class, 'fromView'], $result->items()),
            'pagination' => [
                'page' => $result->page(),
                'limit' => $limit,
                'total' => $total,
                'pages' => $limit > 0 ? (int) ceil($total / $limit) : 0,
            ],
        ];
    }
}
