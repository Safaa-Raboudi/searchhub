<?php

declare(strict_types=1);

namespace App\Document\Application\Create;

use App\Document\Application\DocumentView;
use App\Document\Domain\Document;
use App\Document\Domain\DocumentId;
use App\Document\Domain\DocumentRepositoryInterface;

/**
 * Coordinates the "create a document" use case: generate an identity,
 * build the Document through its own domain rules, persist it. It
 * deliberately contains no business rules of its own — those live in
 * Document::create() (empty title/content/type, tag normalization). If
 * this handler ever finds itself re-checking those conditions, that is a
 * sign a rule leaked out of the Domain layer.
 *
 * This is a plain, directly callable class — not a Symfony Messenger
 * message handler. "Command" here means "an Application-layer request to
 * perform a use case", executed synchronously in the same call as
 * `$handler($command)`. A Messenger message is a different concept:
 * a serializable envelope dispatched onto a bus, potentially handled
 * later, by a different process, after crossing a transport (RabbitMQ).
 * Nothing here is serialized, queued, or asynchronous.
 */
final class CreateDocumentHandler
{
    private DocumentRepositoryInterface $repository;

    public function __construct(DocumentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(CreateDocumentCommand $command): DocumentView
    {
        $document = Document::create(
            DocumentId::generate(),
            $command->title(),
            $command->content(),
            $command->type(),
            $command->tags()
        );

        $this->repository->save($document);

        return DocumentView::fromDocument($document);
    }
}
