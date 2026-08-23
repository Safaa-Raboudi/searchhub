# SearchHub

A backend service for managing documents and providing full-text search, built with Symfony, Doctrine, RabbitMQ and OpenSearch. This is a learning/portfolio project: it starts on Symfony 5.4 / PHP 8.0 and will later be migrated incrementally to Symfony 6.4 and 7.4.

## Status

Phase 0 — project initialization. No business features yet.

## Getting started

Everything runs in Docker (PHP 8.0-fpm, nginx, PostgreSQL 15). No local PHP install is required.

```bash
docker compose build
docker compose up -d
docker compose exec php bin/console about
```

The app is served at http://localhost:8080.

## Tests and static analysis

```bash
docker compose exec php vendor/bin/phpunit
docker compose exec php vendor/bin/phpstan analyse
```
