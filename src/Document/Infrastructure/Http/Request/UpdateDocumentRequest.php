<?php

declare(strict_types=1);

namespace App\Document\Infrastructure\Http\Request;

use App\Document\Application\Update\UpdateDocumentCommand;
use App\Document\Domain\DocumentId;
use App\Document\Infrastructure\Http\Exception\ValidationFailedException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * PATCH semantics require distinguishing "field omitted" from "field
 * explicitly sent" — a plain `$data['title'] ?? null` collapses both
 * into null, which would make an omitted field indistinguishable from
 * one the client asked to be blank. Tracked explicitly here with
 * array_key_exists() instead: no sentinel/marker object needed, and
 * nothing here requires PHP 8.1+.
 *
 * Each present field is validated individually (rather than via
 * attributes on fixed properties, as CreateDocumentRequest does) because
 * presence itself — not just shape — is the first-order concern for a
 * PATCH body; a field that was never sent has nothing to validate.
 */
final class UpdateDocumentRequest
{
    private bool $hasTitle = false;
    private mixed $title = null;
    private bool $hasContent = false;
    private mixed $content = null;
    private bool $hasType = false;
    private mixed $type = null;
    private bool $hasTags = false;
    private mixed $tags = null;

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $request = new self();

        $request->hasTitle = array_key_exists('title', $data);
        $request->title = $data['title'] ?? null;

        $request->hasContent = array_key_exists('content', $data);
        $request->content = $data['content'] ?? null;

        $request->hasType = array_key_exists('type', $data);
        $request->type = $data['type'] ?? null;

        $request->hasTags = array_key_exists('tags', $data);
        $request->tags = $data['tags'] ?? null;

        return $request;
    }

    public function toCommand(DocumentId $id, ValidatorInterface $validator): UpdateDocumentCommand
    {
        $violations = [];

        $title = $this->validateField($validator, 'title', $this->hasTitle, $this->title, [
            new Assert\NotBlank(),
            new Assert\Type('string'),
        ], $violations);

        $content = $this->validateField($validator, 'content', $this->hasContent, $this->content, [
            new Assert\NotBlank(),
            new Assert\Type('string'),
        ], $violations);

        $type = $this->validateField($validator, 'type', $this->hasType, $this->type, [
            new Assert\NotBlank(),
            new Assert\Type('string'),
        ], $violations);

        $tags = $this->validateField($validator, 'tags', $this->hasTags, $this->tags, [
            new Assert\Type('array'),
        ], $violations);

        if ($violations !== []) {
            throw ValidationFailedException::fromFieldMessages($violations);
        }

        /** @var string|null $title */
        /** @var string|null $content */
        /** @var string|null $type */
        /** @var string[]|null $tags */
        return new UpdateDocumentCommand($id, $title, $content, $type, $tags);
    }

    /**
     * @param Constraint[] $constraints
     * @param array<string, string[]> $violations
     */
    private function validateField(
        ValidatorInterface $validator,
        string $field,
        bool $isPresent,
        mixed $value,
        array $constraints,
        array &$violations
    ): mixed {
        if (!$isPresent) {
            return null;
        }

        $result = $validator->validate($value, $constraints);

        if (count($result) > 0) {
            foreach ($result as $violation) {
                $violations[$field][] = (string) $violation->getMessage();
            }

            return null;
        }

        return $value;
    }
}
