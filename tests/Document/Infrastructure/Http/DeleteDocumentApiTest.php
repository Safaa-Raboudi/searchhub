<?php

declare(strict_types=1);

namespace App\Tests\Document\Infrastructure\Http;

use App\Document\Domain\Document;
use App\Document\Domain\DocumentId;

final class DeleteDocumentApiTest extends ApiTestCase
{
    public function testExistingDocumentIsDeletedAndReturns204(): void
    {
        $document = Document::create(DocumentId::generate(), 'Title', 'Content', 'article');
        $this->entityManager->persist($document);
        $this->entityManager->flush();

        $this->client->request('DELETE', '/api/documents/' . $document->id());

        $response = $this->client->getResponse();
        self::assertSame(204, $response->getStatusCode());
        self::assertSame('', $response->getContent());

        $this->entityManager->clear();
        self::assertNull($this->entityManager->find(Document::class, $document->id()));
    }

    public function testUnknownDocumentReturns404(): void
    {
        $this->client->request('DELETE', '/api/documents/' . DocumentId::generate());

        self::assertSame(404, $this->client->getResponse()->getStatusCode());
        self::assertSame('DOCUMENT_NOT_FOUND', $this->jsonResponse()['error']['code']);
    }
}
