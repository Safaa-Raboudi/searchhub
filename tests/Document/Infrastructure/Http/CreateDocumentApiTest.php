<?php

declare(strict_types=1);

namespace App\Tests\Document\Infrastructure\Http;

use App\Document\Domain\Document;
use App\Document\Domain\DocumentId;

final class CreateDocumentApiTest extends ApiTestCase
{
    public function testValidRequestCreatesADraftDocument(): void
    {
        $this->requestJson('POST', '/api/documents', [
            'title' => 'Symfony Messenger',
            'content' => 'Introduction to asynchronous processing',
            'type' => 'article',
            'tags' => ['symfony', 'php'],
        ]);

        $response = $this->client->getResponse();
        self::assertSame(201, $response->getStatusCode());

        $body = $this->jsonResponse();
        self::assertArrayHasKey('id', $body);
        self::assertSame('draft', $body['status']);
        self::assertSame('Symfony Messenger', $body['title']);
        self::assertSame(['symfony', 'php'], $body['tags']);
        self::assertSame('/api/documents/' . $body['id'], $response->headers->get('Location'));

        // Prove it actually landed in PostgreSQL, not just in the response.
        $stored = $this->entityManager->find(Document::class, DocumentId::fromString((string) $body['id']));
        self::assertNotNull($stored);
        self::assertSame('Symfony Messenger', $stored->title());
    }

    public function testBlankTitleIsRejected(): void
    {
        $this->requestJson('POST', '/api/documents', [
            'title' => '   ',
            'content' => 'Content',
            'type' => 'article',
        ]);

        // Passes HTTP shape validation (it IS a non-empty string), fails
        // the Domain invariant — a domain rule violation, not a
        // VALIDATION_FAILED HTTP-shape error.
        self::assertSame(422, $this->client->getResponse()->getStatusCode());
        self::assertSame('DOMAIN_RULE_VIOLATION', $this->jsonResponse()['error']['code']);
    }

    public function testMissingTitleFailsHttpValidation(): void
    {
        $this->requestJson('POST', '/api/documents', [
            'content' => 'Content',
            'type' => 'article',
        ]);

        self::assertSame(422, $this->client->getResponse()->getStatusCode());

        $body = $this->jsonResponse();
        self::assertSame('VALIDATION_FAILED', $body['error']['code']);
        self::assertArrayHasKey('title', $body['error']['violations']);
    }

    public function testBlankContentIsRejected(): void
    {
        $this->requestJson('POST', '/api/documents', [
            'title' => 'Title',
            'content' => '   ',
            'type' => 'article',
        ]);

        self::assertSame(422, $this->client->getResponse()->getStatusCode());
        self::assertSame('DOMAIN_RULE_VIOLATION', $this->jsonResponse()['error']['code']);
    }

    public function testMalformedJsonReturns400(): void
    {
        $this->client->request(
            'POST',
            '/api/documents',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{not valid json'
        );

        self::assertSame(400, $this->client->getResponse()->getStatusCode());
        self::assertSame('MALFORMED_JSON', $this->jsonResponse()['error']['code']);
    }
}
