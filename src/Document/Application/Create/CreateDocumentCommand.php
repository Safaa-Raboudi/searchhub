<?php

declare(strict_types=1);

namespace App\Document\Application\Create;

/**
 * Plain input carrier for the "create a document" use case. PHP 8.0 has no
 * readonly properties, so immutability here is by convention: every
 * property is set once in the constructor and only ever read back through
 * a getter — nothing exposes a setter.
 */
final class CreateDocumentCommand
{
    private string $title;
    private string $content;
    private string $type;

    /** @var string[] */
    private array $tags;

    /**
     * @param string[] $tags
     */
    public function __construct(string $title, string $content, string $type, array $tags = [])
    {
        $this->title = $title;
        $this->content = $content;
        $this->type = $type;
        $this->tags = $tags;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function content(): string
    {
        return $this->content;
    }

    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return string[]
     */
    public function tags(): array
    {
        return $this->tags;
    }
}
