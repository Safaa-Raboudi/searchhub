<?php

declare(strict_types=1);

namespace App\Document\Infrastructure\Http\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Carries HTTP-input validation failures from a Request DTO to the
 * central exception listener, which turns it into a 422 JSON response.
 * This is an HTTP-layer concept, not a Domain exception: it has nothing
 * to do with business rules, only with "the request body/query doesn't
 * have the shape we require".
 */
final class ValidationFailedException extends \RuntimeException
{
    /** @var array<string, string[]> */
    private array $violations;

    /**
     * @param array<string, string[]> $violations
     */
    private function __construct(array $violations)
    {
        parent::__construct('The request contains invalid data.');

        $this->violations = $violations;
    }

    public static function fromConstraintViolationList(ConstraintViolationListInterface $violations): self
    {
        $grouped = [];

        foreach ($violations as $violation) {
            $property = $violation->getPropertyPath();
            $grouped[$property][] = (string) $violation->getMessage();
        }

        return new self($grouped);
    }

    /**
     * @param array<string, string[]> $violations
     */
    public static function fromFieldMessages(array $violations): self
    {
        return new self($violations);
    }

    /**
     * @return array<string, string[]>
     */
    public function violations(): array
    {
        return $this->violations;
    }
}
