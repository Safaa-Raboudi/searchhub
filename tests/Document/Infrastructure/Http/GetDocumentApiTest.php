<?php

declare(strict_types=1);

namespace App\Tests\Document\Infrastructure\Http;

use App\Document\Domain\Document;
use App\Document\Domain\DocumentId;

final class GetDocumentApiTest extends ApiTestCase
{
    public function testExistingDocumentReturns200WithItsData(): void
    {
        $document = Document::create(
            DocumentId::generate(),
            'Understanding DDD',
            'Domain-Driven Design is about modeling the business.',
            'article',
            ['ddd']
        );
        $this->entityManager->persist($document);
        $this->entityManager->flush();

        $this->client->request('GET', '/api/documents/' . $document->id());

        self::assertSame(200, $this->client->getResponse()->getStatusCode());

        $body = $this->jsonResponse();
        self::assertSame((string) $document->id(), $body['id']);
        self::assertSame('Understanding DDD', $body['title']);
        self::assertSame('draft', $body['status']);
    }

    public function testUnknownDocumentReturns404(): void
    {
        $this->client->request('GET', '/api/documents/' . DocumentId::generate());

        self::assertSame(404, $this->client->getResponse()->getStatusCode());
        self::assertSame('DOCUMENT_NOT_FOUND', $this->jsonResponse()['error']['code']);
    }

    public function testMalformedIdReturns404(): void
    {
        $this->client->request('GET', '/api/documents/not-a-uuid');

        self::assertSame(404, $this->client->getResponse()->getStatusCode());
    }
}
