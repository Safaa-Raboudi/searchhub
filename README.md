# SearchHub

SearchHub is a backend-focused document search platform built with Symfony.

It is designed to provide a centralized API for ingesting, managing, indexing,
and searching documents from multiple external systems.

The project focuses on clean architecture, asynchronous processing, search
capabilities, and maintainable backend development.

## Tech Stack

**PHP · Symfony · PostgreSQL · RabbitMQ · OpenSearch · Docker · PHPUnit**

## Architecture

SearchHub follows a DDD-inspired modular architecture with a clear separation
between Domain, Application, and Infrastructure layers.

External systems interact with SearchHub through REST APIs. Documents can be
stored in PostgreSQL and asynchronously indexed in OpenSearch through RabbitMQ
for efficient full-text search.

## Key Features

- REST API for document ingestion and management
- Document metadata management
- Full-text search with OpenSearch
- Search filtering and querying
- Asynchronous document indexing with RabbitMQ
- PostgreSQL persistence
- Modular and maintainable architecture
- Automated testing with PHPUnit
- Dockerized development environment

## Architecture Overview

```text
External Systems
       │
       ▼
   REST API
       │
       ▼
Symfony Application
   │           │
   ▼           ▼
PostgreSQL   RabbitMQ
                │
                ▼
             Worker
                │
                ▼
            OpenSearch
```

## Getting Started

Everything runs in Docker (PHP-FPM, nginx, PostgreSQL). No local PHP install is
required.

```bash
docker compose build
docker compose up -d
docker compose exec php bin/console about
```

The app is served at http://localhost:8080.

## Tests and Static Analysis

```bash
docker compose exec php vendor/bin/phpunit
docker compose exec php vendor/bin/phpstan analyse
```
