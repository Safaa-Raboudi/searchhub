<?php

declare(strict_types=1);

namespace App\Document\Application\Update;

use App\Document\Domain\DocumentId;

/**
 * Unlike CreateDocumentCommand, every field here is optional: null means
 * "leave this field unchanged" (PATCH semantics). This is unambiguous at
 * this layer because, by the time an UpdateDocumentCommand exists, the
 * HTTP layer has already resolved "was this field present in the
 * request?" — a field the client actually sent can never legitimately be
 * null (title/content/type/tags are all non-nullable Domain invariants),
 * so null here only ever means "omitted".
 */
final class UpdateDocumentCommand
{
    private DocumentId $id;
    private ?string $title;
    private ?string $content;
    private ?string $type;

    /** @var string[]|null */
    private ?array $tags;

    /**
     * @param string[]|null $tags
     */
    public function __construct(DocumentId $id, ?string $title, ?string $content, ?string $type, ?array $tags)
    {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->type = $type;
        $this->tags = $tags;
    }

    public function id(): DocumentId
    {
        return $this->id;
    }

    public function title(): ?string
    {
        return $this->title;
    }

    public function content(): ?string
    {
        return $this->content;
    }

    public function type(): ?string
    {
        return $this->type;
    }

    /**
     * @return string[]|null
     */
    public function tags(): ?array
    {
        return $this->tags;
    }
}
