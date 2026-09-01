<?php

declare(strict_types=1);

namespace App\Tests\Document\Infrastructure\Http;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Shared by every Document API functional test: real HTTP requests
 * through the Symfony kernel, real Doctrine, real PostgreSQL test
 * database — no mocked controllers, no mocked EntityManager.
 *
 * Each test runs inside a transaction rolled back in tearDown(), same
 * strategy as Phase 3's DoctrineDocumentRepositoryTest. One added
 * subtlety here: KernelBrowser reboots the kernel (and so builds a new
 * EntityManager/connection) between requests by default, which would
 * silently detach from the transaction begun in setUp(). disableReboot()
 * keeps the same container — and so the same connection — for the whole
 * test, which is what makes the rollback in tearDown() actually undo
 * what the request(s) wrote.
 */
abstract class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->disableReboot();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->entityManager->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $connection = $this->entityManager->getConnection();
        if ($connection->isTransactionActive()) {
            $connection->rollBack();
        }
        $this->entityManager->close();

        parent::tearDown();
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function requestJson(string $method, string $uri, array $payload = []): void
    {
        $this->client->request(
            $method,
            $uri,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function jsonResponse(): array
    {
        $content = $this->client->getResponse()->getContent();
        self::assertIsString($content);

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        return $decoded;
    }
}
