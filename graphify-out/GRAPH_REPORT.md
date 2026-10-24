# Graph Report - prueba_llama_qwe  (2026-10-24)

## Corpus Check
- 84 files · ~35,135 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 946 nodes · 1149 edges · 60 communities (57 shown, 3 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 23 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `2f7c864b`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Book
- CreateBookDTO
- require
- Patrones de Diseño — PHP & Symfony
- Decisiones Arquitectónicas
- Book
- opencode.json
- App\Application\Port\BookChangeTrackerInterface
- Correcciones Realizadas en el Código
- Doctrine\DBAL\Schema\Schema
- 002-Design-Decisions.md
- Book API - Symfony PHP 8.3 + MariaDB + Docker
- knowledge-graph.js
- Symfony\Component\HttpKernel\Event\RequestEvent
- src/Repository/BookRepository.php
- graphify.js
- generate-graph.sh
- PHP & Symfony 7 — Skill de Desarrollo
- Programación Hexagonal — Ports & Adapters
- ✅ Implementado
- Graphify — Grafo de Conocimiento del Proyecto
- Agente de Calidad de Código PHP
- ApiContext
- Plan de Refactorización: Arquitectura Hexagonal + Patrones de Diseño
- API Documentation — Book API
- PHP Code Quality — Skill
- Obsidian Vault — Gestión de Conocimiento Local
- Calidad de Código PHP
- Pre-commit Hook
- Agente de Arquitectura Hexagonal
- OpenCode Project Configuration
- {
- App\Application\Port\BookRepositoryInterface
- Agente de Frontend Symfony
- assets/js/app.js
- public/js/app.js
- Plan: Fix Dashboard Buttons & Prevent Future API Contract Issues
- Plan: Behat + Playwright Testing Implementation
- Kernel.php
- BookChangeTrackerInterface.php
- .opencode/opencode.json
- package.json
- Planning Agent
- composer.json
- require-dev
- config
- scripts
- KernelBootTrait.php
- DashboardController
- Capas de Testing

## God Nodes (most connected - your core abstractions)
1. `Book` - 37 edges
2. `CreateBookDTO` - 25 edges
3. `require` - 20 edges
4. `{` - 19 edges
5. `Book` - 18 edges
6. `BookApplicationServiceTest` - 18 edges
7. `DashboardController` - 16 edges
8. `BookRepository` - 15 edges
9. `Frontend para Symfony 7.1 — Skill de Desarrollo` - 15 edges
10. `UpdateBookDTO` - 14 edges

## Surprising Connections (you probably didn't know these)
- `getBook()` --references--> `Book`  [EXTRACTED]
  src/Application/BookApplicationServiceInterface.php → src/Domain/Entity/Book.php
- `createBook()` --references--> `CreateBookDTO`  [EXTRACTED]
  src/Application/BookApplicationServiceInterface.php → src/Application/DTO/CreateBookDTO.php
- `updateBook()` --references--> `UpdateBookDTO`  [EXTRACTED]
  src/Application/BookApplicationServiceInterface.php → src/Application/DTO/UpdateBookDTO.php
- `getStatistics()` --references--> `BookStatisticsDTO`  [EXTRACTED]
  src/Application/BookStatisticsServiceInterface.php → src/Application/DTO/BookStatisticsDTO.php
- `searchBooks()` --references--> `SearchBooksDTO`  [EXTRACTED]
  src/Application/Port/BookSearchServiceInterface.php → src/Application/DTO/SearchBooksDTO.php

## Import Cycles
- None detected.

## Communities (60 total, 3 thin omitted)

### Community 0 - "Book"
Cohesion: 0.05
Nodes (13): Doctrine\ORM\EntityManagerInterface, DoctrineBook, createBook(), getBook(), updateBook(), delete(), find(), save() (+5 more)

### Community 1 - "CreateBookDTO"
Cohesion: 0.05
Nodes (12): BookApplicationServiceInterface, PHPUnit\Framework\TestCase, BookApplicationService, CreateBookDTO, self, self, UpdateBookDTO, Symfony\Component\EventDispatcher\EventDispatcher (+4 more)

### Community 2 - "require"
Cohesion: 0.10
Nodes (20): require, doctrine/doctrine-bundle, doctrine/doctrine-migrations-bundle, doctrine/orm, ext-ctype, ext-iconv, php, ramsey/uuid-doctrine (+12 more)

### Community 3 - "Patrones de Diseño — PHP & Symfony"
Cohesion: 0.07
Nodes (26): Adapter Pattern, Builder Pattern, Checklist de Patrones, Command Pattern, Cuándo Usar Cada Patrón, D — Dependency Inversion Principle (DIP), Data Transformer (Form/Serializer), Decorator Pattern (+18 more)

### Community 4 - "Decisiones Arquitectónicas"
Cohesion: 0.04
Nodes (43): Arquitectura, Book API — Project Overview, Dashboard API (`/dashboard/api/`), Endpoints Actuales, Estado, Features Implementadas, Objetivo, REST API (`/api/books`) (+35 more)

### Community 6 - "opencode.json"
Cohesion: 0.13
Nodes (14): default_agent, models, options, model, qwen35-35b, apiKey, baseURL, provider (+6 more)

### Community 7 - "App\Application\Port\BookChangeTrackerInterface"
Cohesion: 0.14
Nodes (9): App\Application\Port\BookChangeTrackerInterface, Psr\Log\LoggerInterface, BookChangeTrackerService, DateTimeImmutable, BookCreatedEvent, DateTimeImmutable, BookUpdatedEvent, DateTimeImmutable (+1 more)

### Community 8 - "Correcciones Realizadas en el Código"
Cohesion: 0.08
Nodes (24): 1. ✅ Rate Limiting (anti-abuse), 1. `src/Repository/BookRepository.php` — Import equivocado, 2. `docker-compose.yml` — Clave YAML duplicada `volumes:`, 2. ✅ Security Headers (5 headers de seguridad), 3. `config/packages/framework.yaml` — Claves inválidas, 3. ✅ HTTP Cache en listado, 4. `config/packages/doctrine.yaml` — Configuraciones incompatibles con MariaDB 10.11, 4. ✅ DTO Groups para serialización de respuestas (+16 more)

### Community 9 - "Doctrine\DBAL\Schema\Schema"
Cohesion: 0.27
Nodes (4): Doctrine\DBAL\Schema\Schema, Doctrine\Migrations\AbstractMigration, Version20261020145426, Version20261020152826

### Community 10 - "002-Design-Decisions.md"
Cohesion: 0.05
Nodes (36): ADR-001: Arquitectura Hexagonal, ADR-002: UUID como Primary Key, ADR-003: Title Hash MD5 para Búsquedas, ADR-004: Rate Limiting en Memoria, ADR-005: DTOs con Validación, ADR-006: Value Object Title, ADR-007: Domain Events, ADR-008: Dashboard API Separada (+28 more)

### Community 11 - "Book API - Symfony PHP 8.3 + MariaDB + Docker"
Cohesion: 0.10
Nodes (20): Base de Datos, Book API - Symfony PHP 8.3 + MariaDB + Docker, Comandos Makefile, Configuración, Crear libro (POST) → `201 Created`, Desarrollo, Docker, Ejemplo: Buscar por título (+12 more)

### Community 12 - "knowledge-graph.js"
Cohesion: 0.29
Nodes (6): files, fs, links, nodes, outputPath, path

### Community 13 - "Symfony\Component\HttpKernel\Event\RequestEvent"
Cohesion: 0.38
Nodes (3): RateLimitingMiddleware, SecurityHeadersListener, Symfony\Component\HttpKernel\Event\RequestEvent

### Community 14 - "src/Repository/BookRepository.php"
Cohesion: 0.60
Nodes (3): Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository, Doctrine\Persistence\ManagerRegistry, BookRepository

### Community 22 - "PHP & Symfony 7 — Skill de Desarrollo"
Cohesion: 0.11
Nodes (18): Checklist de Calidad, Controladores (Adaptadores HTTP), Docker / Symfony Comandos Útiles, DTOs (Data Transfer Objects), Ejemplo de Controlador, Entities (Dominio), Estructura Hexagonal de Archivos, PHP & Symfony 7 — Skill de Desarrollo (+10 more)

### Community 23 - "Programación Hexagonal — Ports & Adapters"
Cohesion: 0.11
Nodes (17): Anti-patrones a Evitar, Capa 1: Dominio (Domain Layer), Capa 2: Aplicación (Application Layer), Capa 3: Adaptadores (Adapter Layer), Capas de la Arquitectura, Checklist de Arquitectura Hexagonal, Dependency Injection Container (Symfony), Filosofía (+9 more)

### Community 24 - "✅ Implementado"
Cohesion: 0.12
Nodes (16): 10. Errores corregidos, 1. PHP actualizado a 8.3.36, 2. Dependencias añadidas, 3. PHPStan configurado, 4. PHP-CS-Fixer configurado, 5. Pre-commit hook creado, 6. Makefile actualizado, 7. Agente code-quality creado (+8 more)

### Community 25 - "Graphify — Grafo de Conocimiento del Proyecto"
Cohesion: 0.13
Nodes (14): Actualizar grafo, Análisis arquitectónico, Archivos Generados, Beneficios, Comandos Principales, Configuración, Consultar grafo, Flujo de Trabajo (+6 more)

### Community 26 - "Agente de Calidad de Código PHP"
Cohesion: 0.13
Nodes (14): 1. PHP-CS-Fixer (Automático), 2. PHPStan (Manual + IA), 3. Corrección Manual de PHPStan, Agente de Calidad de Código PHP, Checklist Pre-Commit, Errores de Null, Errores de Parámetros, Errores de Tipo (+6 more)

### Community 27 - "ApiContext"
Cohesion: 0.10
Nodes (5): App\Tests\Behat\KernelBootTrait, Behat\Gherkin\Node\PyStringNode, PHPUnit\Framework\Assert, ApiContext, BookContext

### Community 28 - "Plan de Refactorización: Arquitectura Hexagonal + Patrones de Diseño"
Cohesion: 0.14
Nodes (13): 10. Decisiones Pendientes, 11. Estado Actual del Proyecto, 12. Próximos Pasos, 1. Nueva Estructura de Directorios, 2. Capa de Dominio (Framework-Independent), 3. Capa de Aplicación (Ports), 4. Capa de Infraestructura (Adapters), 5. Patrones de Diseño Aplicados (+5 more)

### Community 29 - "API Documentation — Book API"
Cohesion: 0.09
Nodes (21): 400 Bad Request, 404 Not Found, 422 Unprocessable Entity, 429 Too Many Requests, API Documentation — Book API, Base URLs, Dashboard API Endpoints (`/dashboard/api/`), DELETE /dashboard/api/books/{id} (+13 more)

### Community 30 - "PHP Code Quality — Skill"
Cohesion: 0.15
Nodes (12): 1. PHPStan (Análisis Estático), 2. PHP-CS-Fixer (Estilo de Código), 3. Pre-commit Hook, Checklist Pre-Commit, Comandos Makefile, Código estilo inconsistente, Errores persistentes de PHPStan, Herramientas de Calidad (+4 more)

### Community 31 - "Obsidian Vault — Gestión de Conocimiento Local"
Cohesion: 0.18
Nodes (10): Comandos principales, Convenciones de Nombres, Estructura del Vault, Flujo de Trabajo, Integración con Graphify, Notas de Decisión de Arquitectura (ADR), Obsidian Vault — Gestión de Conocimiento Local, Propósito (+2 more)

### Community 32 - "Calidad de Código PHP"
Cohesion: 0.20
Nodes (9): Agentes y Skills, Calidad de Código PHP, Comandos, Configuración, Docker, PHP-CS-Fixer, PHPStan, Pre-commit Hook (+1 more)

### Community 33 - "Pre-commit Hook"
Cohesion: 0.25
Nodes (7): Comandos de Corrección, Cómo Funciona, Integración con Agentes IA, Pre-commit Hook, Propósito, Referencias, Saltar el Hook

### Community 34 - "Agente de Arquitectura Hexagonal"
Cohesion: 0.29
Nodes (6): Agente de Arquitectura Hexagonal, Checklist de Revisión, Referencias, Reglas Estrictas, Responsabilidades, Rol

### Community 35 - "OpenCode Project Configuration"
Cohesion: 0.05
Nodes (37): API Contract Verification, API Endpoints, Available Agents, Available Skills, Before Coding, Before Committing, Benefits, code-quality (+29 more)

### Community 36 - "{"
Cohesion: 0.05
Nodes (41): {, }, 1. Check route exists, 2. Test endpoint manually, 3. Verify JavaScript uses matching fields, app_dashboard_api_search  GET  /dashboard/api/search, "books": [...],, CSS Architecture (+33 more)

### Community 37 - "App\Application\Port\BookRepositoryInterface"
Cohesion: 0.12
Nodes (9): App\Application\Port\BookRepositoryInterface, BookStatisticsServiceInterface, BookSearchService, BookStatisticsService, getStatistics(), BookStatisticsDTO, self, SearchBooksDTO (+1 more)

### Community 38 - "Agente de Frontend Symfony"
Cohesion: 0.07
Nodes (29): 1. Planificar la Interfaz, 2. Crear Componentes Reutilizables, 3. Implementar la Página, 4. CSS Architecture, Agente de Frontend Symfony, Checklist de Revisión Frontend, Comandos Útiles, CSS (+21 more)

### Community 39 - "assets/js/app.js"
Cohesion: 0.17
Nodes (16): API, closeDeleteModal(), closeModal(), deleteBook(), handleBookCreated(), handleBookDeleted(), handleBookUpdated(), handleSubmit() (+8 more)

### Community 40 - "public/js/app.js"
Cohesion: 0.17
Nodes (16): API, closeDeleteModal(), closeModal(), deleteBook(), handleBookCreated(), handleBookDeleted(), handleBookUpdated(), handleSubmit() (+8 more)

### Community 41 - "Plan: Fix Dashboard Buttons & Prevent Future API Contract Issues"
Cohesion: 0.10
Nodes (19): 1.1 Fix loadBooks function (lines ~195-210), 1.2 Verify API methods work correctly, 2.1 Create test class, 2.2 Run tests, 3.1 Create test class, 3.2 Install Playwright (optional), 4.1 Add new section: "API Contract Verification", 5.1 Update "Mandatory Dual-Agent Validation Gate" (+11 more)

### Community 42 - "Plan: Behat + Playwright Testing Implementation"
Cohesion: 0.09
Nodes (22): 0.1 Instalar dependencias, 0.2 Crear archivos de configuración, 0.3 Crear directorios, 1.1 Crear KernelBootTrait, 1.2 Crear ApiContext, 1.3 Crear BookContext, 1.4 Crear feature files, 2.1 Añadir data-testid al template (+14 more)

### Community 43 - "Kernel.php"
Cohesion: 0.40
Nodes (3): Kernel, Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait, Symfony\Component\HttpKernel\Kernel

### Community 44 - "BookChangeTrackerInterface.php"
Cohesion: 0.40
Nodes (3): notifyBookCreated(), notifyBookUpdated(), DateTimeImmutable

### Community 45 - ".opencode/opencode.json"
Cohesion: 0.50
Nodes (3): plugin, $schema, .opencode/plugins/graphify.js

### Community 46 - "package.json"
Cohesion: 0.10
Nodes (20): author, bugs, url, description, devDependencies, playwright, directories, test (+12 more)

### Community 47 - "Planning Agent"
Cohesion: 0.13
Nodes (14): 1. Minimal Context Per Phase, 2. Phase Completion Markers, 3. Phase Dependencies, 4. Clear File Inventory, Agent Workflow, Anti-Patterns to Avoid, Core Principles, Example Patterns (+6 more)

### Community 48 - "composer.json"
Cohesion: 0.22
Nodes (8): autoload, autoload-dev, psr-4, psr-4, license, App\\, App\\Tests\\, type

### Community 49 - "require-dev"
Cohesion: 0.25
Nodes (8): require-dev, behat/behat, friendsofphp/php-cs-fixer, phpstan/phpstan, phpunit/phpunit, symfony/browser-kit, symfony/http-client, symfony/maker-bundle

### Community 50 - "config"
Cohesion: 0.29
Nodes (7): symfony/flex, symfony/runtime, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 51 - "scripts"
Cohesion: 0.33
Nodes (7): assets:install %PUBLIC_DIR%, cache:clear, scripts, auto-scripts, post-install-cmd, post-update-cmd, @auto-scripts

### Community 52 - "KernelBootTrait.php"
Cohesion: 0.60
Nodes (4): Symfony\Bundle\FrameworkBundle\KernelBrowser, Symfony\Component\DependencyInjection\ContainerInterface, bootClient(), getContainer()

### Community 58 - "DashboardController"
Cohesion: 0.17
Nodes (11): App\Application\BookApplicationServiceInterface, App\Application\BookStatisticsServiceInterface, App\Application\Port\BookSearchServiceInterface, Response, BookController, DashboardController, Symfony\Bundle\FrameworkBundle\Controller\AbstractController, Symfony\Component\HttpFoundation\JsonResponse (+3 more)

### Community 59 - "Capas de Testing"
Cohesion: 0.20
Nodes (9): 1. PHPUnit — Unit Tests, 2. Behat — API BDD Tests, 3. Playwright — E2E Tests, 4. Full Stack, Capas de Testing, data-testid, Herramientas, Makefile Targets (+1 more)

## Knowledge Gaps
- **443 isolated node(s):** `$schema`, `.opencode/plugins/graphify.js`, `generate-graph.sh script`, `fs`, `path` (+438 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **3 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Book` connect `Book` to `CreateBookDTO`, `DashboardController`?**
  _High betweenness centrality (0.022) - this node is a cross-community bridge._
- **Why does `CreateBookDTO` connect `CreateBookDTO` to `Book`, `DashboardController`?**
  _High betweenness centrality (0.008) - this node is a cross-community bridge._
- **Why does `Book` connect `Book` to `Book`?**
  _High betweenness centrality (0.007) - this node is a cross-community bridge._
- **Are the 17 inferred relationships involving `CreateBookDTO` (e.g. with `.create()` and `.createBook()`) actually correct?**
  _`CreateBookDTO` has 17 INFERRED edges - model-reasoned connections that need verification._
- **What connects `$schema`, `.opencode/plugins/graphify.js`, `generate-graph.sh script` to the rest of the system?**
  _443 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Book` be split into smaller, more focused modules?**
  _Cohesion score 0.051715309779825906 - nodes in this community are weakly interconnected._
- **Should `CreateBookDTO` be split into smaller, more focused modules?**
  _Cohesion score 0.05325814536340852 - nodes in this community are weakly interconnected._