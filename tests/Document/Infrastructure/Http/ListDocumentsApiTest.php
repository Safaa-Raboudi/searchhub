<?php

declare(strict_types=1);

namespace App\Tests\Document\Infrastructure\Http;

use App\Document\Domain\Document;
use App\Document\Domain\DocumentId;

final class ListDocumentsApiTest extends ApiTestCase
{
    private function persistDocuments(int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $this->entityManager->persist(
                Document::create(DocumentId::generate(), "Title {$i}", "Content {$i}", 'article')
            );
        }
        $this->entityManager->flush();
    }

    public function testListsStoredDocumentsWithDefaultPagination(): void
    {
        $this->persistDocuments(3);

        $this->client->request('GET', '/api/documents');

        self::assertSame(200, $this->client->getResponse()->getStatusCode());

        $body = $this->jsonResponse();
        self::assertArrayHasKey('data', $body);
        self::assertArrayHasKey('pagination', $body);
        self::assertCount(3, $body['data']);
        self::assertSame(['page' => 1, 'limit' => 20, 'total' => 3, 'pages' => 1], $body['pagination']);
    }

    public function testPaginationSplitsResultsAcrossPages(): void
    {
        $this->persistDocuments(5);

        $this->client->request('GET', '/api/documents?page=1&limit=2');
        $firstPage = $this->jsonResponse();
        self::assertCount(2, $firstPage['data']);
        self::assertSame(['page' => 1, 'limit' => 2, 'total' => 5, 'pages' => 3], $firstPage['pagination']);

        $this->client->request('GET', '/api/documents?page=3&limit=2');
        $lastPage = $this->jsonResponse();
        self::assertCount(1, $lastPage['data']);
        self::assertSame(3, $lastPage['pagination']['page']);
    }

    public function testEachDocumentInTheListHasTheStableResponseShape(): void
    {
        $this->persistDocuments(1);

        $this->client->request('GET', '/api/documents');

        $item = $this->jsonResponse()['data'][0];
        self::assertSame(
            ['id', 'title', 'content', 'type', 'tags', 'status', 'createdAt', 'updatedAt'],
            array_keys($item)
        );
    }

    public function testInvalidPageIsRejected(): void
    {
        $this->client->request('GET', '/api/documents?page=0');

        self::assertSame(422, $this->client->getResponse()->getStatusCode());
        self::assertSame('VALIDATION_FAILED', $this->jsonResponse()['error']['code']);
    }

    public function testLimitAboveMaximumIsRejected(): void
    {
        $this->client->request('GET', '/api/documents?limit=1000');

        self::assertSame(422, $this->client->getResponse()->getStatusCode());
    }
}
