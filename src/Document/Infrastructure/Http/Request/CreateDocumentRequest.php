<?php

declare(strict_types=1);

namespace App\Document\Infrastructure\Http\Request;

use App\Document\Application\Create\CreateDocumentCommand;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Fields are typed `mixed` rather than string/array on purpose: the raw
 * decoded JSON body can contain any shape (a client might send a number
 * for "title", or a string for "tags"), and if the properties were typed
 * string/array directly, a wrong-shaped payload would throw a PHP
 * TypeError before validation ever ran (strict_types=1), producing an
 * uncontrolled 500 instead of a clean 422 with a useful message. Letting
 * Symfony Validator's Type constraint check the actual runtime shape
 * keeps that failure mode a normal validation error.
 *
 * This mirrors, at the HTTP boundary, the same "check real boundaries
 * for real" approach Document::normalizeTags() already takes at the
 * Domain boundary — same idea, applied one layer further out.
 */
final class CreateDocumentRequest
{
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    private mixed $title = null;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    private mixed $content = null;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    private mixed $type = null;

    /**
     * Only the shape is validated here ("is this an array at all?") —
     * an HTTP-input concern. Whether each individual tag is a meaningful
     * non-blank string is already an existing, tested Domain invariant
     * (Document::normalizeTags(), Phase 1), deliberately not duplicated
     * here: a blank tag surfaces as a domain rule violation (422), not
     * an HTTP validation error, and that's the correct boundary for it.
     */
    #[Assert\Type('array')]
    private mixed $tags = [];

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $request = new self();
        $request->title = $data['title'] ?? null;
        $request->content = $data['content'] ?? null;
        $request->type = $data['type'] ?? null;
        $request->tags = $data['tags'] ?? [];

        return $request;
    }

    /**
     * Only safe to call once validation has confirmed the fields are
     * actually string/string[] — see the class docblock above.
     */
    public function toCommand(): CreateDocumentCommand
    {
        $title = $this->title;
        $content = $this->content;
        $type = $this->type;
        $tags = $this->tags;

        if (!is_string($title) || !is_string($content) || !is_string($type) || !is_array($tags)) {
            throw new \LogicException('toCommand() must only be called after successful validation.');
        }

        /** @var string[] $tags */
        return new CreateDocumentCommand($title, $content, $type, $tags);
    }
}
