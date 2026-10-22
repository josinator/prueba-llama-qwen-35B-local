# Graph Report - .  (2026-10-22)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 262 nodes · 371 edges · 22 communities (18 shown, 4 thin omitted)
- Extraction: 95% EXTRACTED · 5% INFERRED · 0% AMBIGUOUS · INFERRED: 20 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `a6527319`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Book
- CreateBookDTO
- composer.json
- require
- BookController
- Book
- opencode.json
- BookCreatedEvent
- UpdateBookDTO
- Doctrine\DBAL\Schema\Schema
- BookRepository
- TitleTest
- knowledge-graph.js
- Symfony\Component\HttpKernel\Event\RequestEvent
- src/Repository/BookRepository.php
- graphify.js
- generate-graph.sh

## God Nodes (most connected - your core abstractions)
1. `Book` - 35 edges
2. `CreateBookDTO` - 24 edges
3. `require` - 19 edges
4. `Book` - 18 edges
5. `BookApplicationServiceTest` - 18 edges
6. `UpdateBookDTO` - 13 edges
7. `Title` - 12 edges
8. `BookRepository` - 11 edges
9. `BookApplicationService` - 10 edges
10. `InMemoryBookRepository` - 10 edges

## Surprising Connections (you probably didn't know these)
- `getBook()` --references--> `Book`  [EXTRACTED]
  src/Application/BookApplicationServiceInterface.php → src/Domain/Entity/Book.php
- `createBook()` --references--> `CreateBookDTO`  [EXTRACTED]
  src/Application/BookApplicationServiceInterface.php → src/Application/DTO/CreateBookDTO.php
- `createBook()` --references--> `Book`  [EXTRACTED]
  src/Application/BookApplicationServiceInterface.php → src/Domain/Entity/Book.php
- `updateBook()` --references--> `Book`  [EXTRACTED]
  src/Application/BookApplicationServiceInterface.php → src/Domain/Entity/Book.php
- `save()` --references--> `Book`  [EXTRACTED]
  src/Application/Port/BookRepositoryInterface.php → src/Domain/Entity/Book.php

## Import Cycles
- None detected.

## Communities (22 total, 4 thin omitted)

### Community 0 - "Book"
Cohesion: 0.07
Nodes (11): App\Application\Port\BookRepositoryInterface, BookApplicationServiceInterface, BookApplicationService, delete(), find(), save(), Book, DateTimeImmutable (+3 more)

### Community 1 - "CreateBookDTO"
Cohesion: 0.10
Nodes (6): PHPUnit\Framework\TestCase, CreateBookDTO, self, Symfony\Component\EventDispatcher\EventDispatcher, CreateBookDTOTest, BookApplicationServiceTest

### Community 2 - "composer.json"
Cohesion: 0.07
Nodes (28): symfony/flex, symfony/runtime, assets:install %PUBLIC_DIR%, cache:clear, autoload, autoload-dev, psr-4, psr-4 (+20 more)

### Community 3 - "require"
Cohesion: 0.11
Nodes (19): require, doctrine/doctrine-bundle, doctrine/doctrine-migrations-bundle, doctrine/orm, ext-ctype, ext-iconv, php, ramsey/uuid-doctrine (+11 more)

### Community 4 - "BookController"
Cohesion: 0.21
Nodes (8): App\Application\BookApplicationServiceInterface, BookController, Kernel, Symfony\Bundle\FrameworkBundle\Controller\AbstractController, Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait, Symfony\Component\HttpFoundation\JsonResponse, Symfony\Component\HttpFoundation\Request, Symfony\Component\HttpKernel\Kernel

### Community 6 - "opencode.json"
Cohesion: 0.13
Nodes (14): default_agent, models, options, model, qwen35-35b, apiKey, baseURL, provider (+6 more)

### Community 7 - "BookCreatedEvent"
Cohesion: 0.21
Nodes (6): Psr\Log\LoggerInterface, BookCreatedEvent, DateTimeImmutable, BookUpdatedEvent, DateTimeImmutable, DomainEventListener

### Community 8 - "UpdateBookDTO"
Cohesion: 0.17
Nodes (5): createBook(), getBook(), updateBook(), self, UpdateBookDTO

### Community 9 - "Doctrine\DBAL\Schema\Schema"
Cohesion: 0.27
Nodes (4): Doctrine\DBAL\Schema\Schema, Doctrine\Migrations\AbstractMigration, Version20261020145426, Version20261020152826

### Community 10 - "BookRepository"
Cohesion: 0.30
Nodes (3): Doctrine\ORM\EntityManagerInterface, DoctrineBook, BookRepository

### Community 12 - "knowledge-graph.js"
Cohesion: 0.29
Nodes (6): files, fs, links, nodes, outputPath, path

### Community 13 - "Symfony\Component\HttpKernel\Event\RequestEvent"
Cohesion: 0.38
Nodes (3): RateLimitingMiddleware, SecurityHeadersListener, Symfony\Component\HttpKernel\Event\RequestEvent

### Community 14 - "src/Repository/BookRepository.php"
Cohesion: 0.60
Nodes (3): Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository, Doctrine\Persistence\ManagerRegistry, BookRepository

## Knowledge Gaps
- **48 isolated node(s):** `generate-graph.sh script`, `fs`, `path`, `files`, `nodes` (+43 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **4 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Book` connect `Book` to `UpdateBookDTO`, `BookRepository`?**
  _High betweenness centrality (0.168) - this node is a cross-community bridge._
- **Why does `CreateBookDTO` connect `CreateBookDTO` to `Book`, `UpdateBookDTO`, `BookController`?**
  _High betweenness centrality (0.090) - this node is a cross-community bridge._
- **Why does `Book` connect `Book` to `BookRepository`?**
  _High betweenness centrality (0.070) - this node is a cross-community bridge._
- **Are the 16 inferred relationships involving `CreateBookDTO` (e.g. with `.create()` and `.fromArrayCreatesDto()`) actually correct?**
  _`CreateBookDTO` has 16 INFERRED edges - model-reasoned connections that need verification._
- **What connects `generate-graph.sh script`, `fs`, `path` to the rest of the system?**
  _48 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Book` be split into smaller, more focused modules?**
  _Cohesion score 0.0708245243128964 - nodes in this community are weakly interconnected._
- **Should `CreateBookDTO` be split into smaller, more focused modules?**
  _Cohesion score 0.10080645161290322 - nodes in this community are weakly interconnected._