<?php

declare(strict_types=1);

namespace App\Document\Domain;

use App\Document\Domain\Exception\InvalidDocumentContent;
use App\Document\Domain\Exception\InvalidDocumentStatusTransition;
use App\Document\Domain\Exception\InvalidDocumentTags;
use App\Document\Domain\Exception\InvalidDocumentTitle;
use App\Document\Domain\Exception\InvalidDocumentType;

final class Document
{
    private DocumentId $id;
    private string $title;
    private string $content;
    private string $type;

    /** @var string[] */
    private array $tags;

    private DocumentStatus $status;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    /**
     * @param string[] $tags
     */
    private function __construct(
        DocumentId $id,
        string $title,
        string $content,
        string $type,
        array $tags,
        DocumentStatus $status,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->content = $content;
        $this->type = $type;
        $this->tags = $tags;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    /**
     * @param string[] $tags
     */
    public static function create(
        DocumentId $id,
        string $title,
        string $content,
        string $type,
        array $tags = []
    ): self {
        self::assertTitleIsValid($title);
        self::assertContentIsValid($content);
        self::assertTypeIsValid($type);

        $now = new \DateTimeImmutable();

        return new self(
            $id,
            $title,
            $content,
            $type,
            self::normalizeTags($tags),
            DocumentStatus::draft(),
            $now,
            $now
        );
    }

    public function id(): DocumentId
    {
        return $this->id;
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

    public function status(): DocumentStatus
    {
        return $this->status;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function changeTitle(string $title): void
    {
        self::assertTitleIsValid($title);
        $this->title = $title;
        $this->touch();
    }

    public function changeContent(string $content): void
    {
        self::assertContentIsValid($content);
        $this->content = $content;
        $this->touch();
    }

    public function changeType(string $type): void
    {
        self::assertTypeIsValid($type);
        $this->type = $type;
        $this->touch();
    }

    /**
     * @param string[] $tags
     */
    public function replaceTags(array $tags): void
    {
        $this->tags = self::normalizeTags($tags);
        $this->touch();
    }

    public function publish(): void
    {
        $this->transitionTo(DocumentStatus::published());
    }

    public function archive(): void
    {
        $this->transitionTo(DocumentStatus::archived());
    }

    private function transitionTo(DocumentStatus $target): void
    {
        if (!$this->status->canTransitionTo($target)) {
            throw InvalidDocumentStatusTransition::fromTo($this->status, $target);
        }

        $this->status = $target;
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    private static function assertTitleIsValid(string $title): void
    {
        if (trim($title) === '') {
            throw InvalidDocumentTitle::cannotBeEmpty();
        }
    }

    private static function assertContentIsValid(string $content): void
    {
        if (trim($content) === '') {
            throw InvalidDocumentContent::cannotBeEmpty();
        }
    }

    private static function assertTypeIsValid(string $type): void
    {
        if (trim($type) === '') {
            throw InvalidDocumentType::cannotBeEmpty();
        }
    }

    /**
     * The incoming array is only typed as string[] by convention at the
     * public API (create/replaceTags); nothing at runtime stops a caller
     * from passing other scalar types, so this boundary is checked for
     * real instead of trusting the docblock.
     *
     * @param mixed[] $tags
     * @return string[]
     */
    private static function normalizeTags(array $tags): array
    {
        foreach ($tags as $tag) {
            if (!is_string($tag) || trim($tag) === '') {
                throw InvalidDocumentTags::cannotContainEmptyValue();
            }
        }

        return array_values(array_unique($tags));
    }
}
