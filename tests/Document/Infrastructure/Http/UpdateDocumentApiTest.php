<?php

declare(strict_types=1);

namespace App\Tests\Document\Infrastructure\Http;

use App\Document\Domain\Document;
use App\Document\Domain\DocumentId;

final class UpdateDocumentApiTest extends ApiTestCase
{
    private function persistDocument(): Document
    {
        $document = Document::create(
            DocumentId::generate(),
            'Original Title',
            'Original content.',
            'article',
            ['original']
        );
        $this->entityManager->persist($document);
        $this->entityManager->flush();

        return $document;
    }

    public function testPartialUpdateChangesOnlyGivenFields(): void
    {
        $document = $this->persistDocument();

        $this->requestJson('PATCH', '/api/documents/' . $document->id(), [
            'title' => 'New Title',
        ]);

        self::assertSame(200, $this->client->getResponse()->getStatusCode());

        $body = $this->jsonResponse();
        self::assertSame('New Title', $body['title']);
        // Omitted fields must survive untouched.
        self::assertSame('Original content.', $body['content']);
        self::assertSame('article', $body['type']);
        self::assertSame(['original'], $body['tags']);
    }

    public function testOmittedFieldsAreNotOverwrittenWithNull(): void
    {
        $document = $this->persistDocument();

        $this->requestJson('PATCH', '/api/documents/' . $document->id(), [
            'tags' => ['updated'],
        ]);

        $this->entityManager->clear();
        $reloaded = $this->entityManager->find(Document::class, $document->id());

        self::assertNotNull($reloaded);
        self::assertSame('Original Title', $reloaded->title());
        self::assertSame('Original content.', $reloaded->content());
        self::assertSame(['updated'], $reloaded->tags());
    }

    public function testBlankValueForAProvidedFieldIsRejected(): void
    {
        $document = $this->persistDocument();

        $this->requestJson('PATCH', '/api/documents/' . $document->id(), [
            'title' => '   ',
        ]);

        self::assertSame(422, $this->client->getResponse()->getStatusCode());
        self::assertSame('DOMAIN_RULE_VIOLATION', $this->jsonResponse()['error']['code']);
    }

    public function testWrongTypeForAProvidedFieldFailsHttpValidation(): void
    {
        $document = $this->persistDocument();

        $this->requestJson('PATCH', '/api/documents/' . $document->id(), [
            'title' => 12345,
        ]);

        self::assertSame(422, $this->client->getResponse()->getStatusCode());
        self::assertSame('VALIDATION_FAILED', $this->jsonResponse()['error']['code']);
    }

    public function testUnknownDocumentReturns404(): void
    {
        $this->requestJson('PATCH', '/api/documents/' . DocumentId::generate(), [
            'title' => 'New Title',
        ]);

        self::assertSame(404, $this->client->getResponse()->getStatusCode());
        self::assertSame('DOCUMENT_NOT_FOUND', $this->jsonResponse()['error']['code']);
    }
}
