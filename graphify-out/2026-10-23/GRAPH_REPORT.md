# Graph Report - prueba_llama_qwe  (2026-10-22)

## Corpus Check
- 58 files · ~17,393 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 557 nodes · 649 edges · 36 communities (32 shown, 4 thin omitted)
- Extraction: 97% EXTRACTED · 3% INFERRED · 0% AMBIGUOUS · INFERRED: 20 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `a6527319`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- Book
- CreateBookDTO
- require
- Patrones de Diseño — PHP & Symfony
- Book API — Project Overview
- Book
- opencode.json
- BookCreatedEvent
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
- Patrones de Diseño Aplicados
- Plan de Refactorización: Arquitectura Hexagonal + Patrones de Diseño
- API Documentation — Book API
- PHP Code Quality — Skill
- Obsidian Vault — Gestión de Conocimiento Local
- Calidad de Código PHP
- Pre-commit Hook
- Agente de Arquitectura Hexagonal
- AGENTS.md

## God Nodes (most connected - your core abstractions)
1. `Book` - 35 edges
2. `CreateBookDTO` - 24 edges
3. `require` - 19 edges
4. `Book` - 18 edges
5. `BookApplicationServiceTest` - 18 edges
6. `Programación Hexagonal — Ports & Adapters` - 14 edges
7. `UpdateBookDTO` - 13 edges
8. `Plan de Refactorización: Arquitectura Hexagonal + Patrones de Diseño` - 13 edges
9. `Title` - 12 edges
10. `BookRepository` - 11 edges

## Surprising Connections (you probably didn't know these)
- `getBook()` --references--> `Book`  [EXTRACTED]
  src/Application/BookApplicationServiceInterface.php → src/Domain/Entity/Book.php
- `createBook()` --references--> `CreateBookDTO`  [EXTRACTED]
  src/Application/BookApplicationServiceInterface.php → src/Application/DTO/CreateBookDTO.php
- `updateBook()` --references--> `UpdateBookDTO`  [EXTRACTED]
  src/Application/BookApplicationServiceInterface.php → src/Application/DTO/UpdateBookDTO.php
- `save()` --references--> `Book`  [EXTRACTED]
  src/Application/Port/BookRepositoryInterface.php → src/Domain/Entity/Book.php
- `delete()` --references--> `Book`  [EXTRACTED]
  src/Application/Port/BookRepositoryInterface.php → src/Domain/Entity/Book.php

## Import Cycles
- None detected.

## Communities (36 total, 4 thin omitted)

### Community 0 - "Book"
Cohesion: 0.05
Nodes (17): App\Application\Port\BookRepositoryInterface, BookApplicationServiceInterface, Doctrine\ORM\EntityManagerInterface, DoctrineBook, BookApplicationService, createBook(), getBook(), updateBook() (+9 more)

### Community 1 - "CreateBookDTO"
Cohesion: 0.05
Nodes (17): App\Application\BookApplicationServiceInterface, PHPUnit\Framework\TestCase, CreateBookDTO, self, self, UpdateBookDTO, BookController, Kernel (+9 more)

### Community 2 - "require"
Cohesion: 0.04
Nodes (47): symfony/flex, symfony/runtime, assets:install %PUBLIC_DIR%, cache:clear, autoload, autoload-dev, psr-4, psr-4 (+39 more)

### Community 3 - "Patrones de Diseño — PHP & Symfony"
Cohesion: 0.07
Nodes (26): Adapter Pattern, Builder Pattern, Checklist de Patrones, Command Pattern, Cuándo Usar Cada Patrón, D — Dependency Inversion Principle (DIP), Data Transformer (Form/Serializer), Decorator Pattern (+18 more)

### Community 4 - "Book API — Project Overview"
Cohesion: 0.08
Nodes (22): Arquitectura, Book API — Project Overview, Endpoints Actuales, Estado, Features Implementadas, Objetivo, Stack Tecnológico, 1. Arquitectura Hexagonal (Ports & Adapters) (+14 more)

### Community 6 - "opencode.json"
Cohesion: 0.13
Nodes (14): default_agent, models, options, model, qwen35-35b, apiKey, baseURL, provider (+6 more)

### Community 7 - "BookCreatedEvent"
Cohesion: 0.21
Nodes (6): Psr\Log\LoggerInterface, BookCreatedEvent, DateTimeImmutable, BookUpdatedEvent, DateTimeImmutable, DomainEventListener

### Community 8 - "Correcciones Realizadas en el Código"
Cohesion: 0.08
Nodes (24): 1. ✅ Rate Limiting (anti-abuse), 1. `src/Repository/BookRepository.php` — Import equivocado, 2. `docker-compose.yml` — Clave YAML duplicada `volumes:`, 2. ✅ Security Headers (5 headers de seguridad), 3. `config/packages/framework.yaml` — Claves inválidas, 3. ✅ HTTP Cache en listado, 4. `config/packages/doctrine.yaml` — Configuraciones incompatibles con MariaDB 10.11, 4. ✅ DTO Groups para serialización de respuestas (+16 more)

### Community 9 - "Doctrine\DBAL\Schema\Schema"
Cohesion: 0.27
Nodes (4): Doctrine\DBAL\Schema\Schema, Doctrine\Migrations\AbstractMigration, Version20261020145426, Version20261020152826

### Community 10 - "002-Design-Decisions.md"
Cohesion: 0.10
Nodes (20): ADR-001: Arquitectura Hexagonal, ADR-002: UUID como Primary Key, ADR-003: Title Hash MD5 para Búsquedas, ADR-004: Rate Limiting en Memoria, ADR-005: DTOs con Validación, Consecuencias, Consecuencias, Consecuencias (+12 more)

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

### Community 27 - "Patrones de Diseño Aplicados"
Cohesion: 0.14
Nodes (12): Conductuales, Creacionales, Estructurales, Patrones de Diseño Aplicados, Patrones GoF Usados, Patrones Symfony Usados, Próximos Patrones a Implementar, SOLID (+4 more)

### Community 28 - "Plan de Refactorización: Arquitectura Hexagonal + Patrones de Diseño"
Cohesion: 0.14
Nodes (13): 10. Decisiones Pendientes, 11. Estado Actual del Proyecto, 12. Próximos Pasos, 1. Nueva Estructura de Directorios, 2. Capa de Dominio (Framework-Independent), 3. Capa de Aplicación (Ports), 4. Capa de Infraestructura (Adapters), 5. Patrones de Diseño Aplicados (+5 more)

### Community 29 - "API Documentation — Book API"
Cohesion: 0.15
Nodes (12): 400 Bad Request, 404 Not Found, 429 Too Many Requests, API Documentation — Book API, Base URL, Endpoints, Error Responses, GET /books (+4 more)

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

## Knowledge Gaps
- **262 isolated node(s):** `generate-graph.sh script`, `fs`, `path`, `files`, `nodes` (+257 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **4 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `CreateBookDTO` connect `CreateBookDTO` to `Book`?**
  _High betweenness centrality (0.020) - this node is a cross-community bridge._
- **Why does `Book` connect `Book` to `Book`?**
  _High betweenness centrality (0.015) - this node is a cross-community bridge._
- **Are the 16 inferred relationships involving `CreateBookDTO` (e.g. with `.create()` and `.fromArrayCreatesDto()`) actually correct?**
  _`CreateBookDTO` has 16 INFERRED edges - model-reasoned connections that need verification._
- **What connects `generate-graph.sh script`, `fs`, `path` to the rest of the system?**
  _262 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Book` be split into smaller, more focused modules?**
  _Cohesion score 0.05499735589635114 - nodes in this community are weakly interconnected._
- **Should `CreateBookDTO` be split into smaller, more focused modules?**
  _Cohesion score 0.05144230769230769 - nodes in this community are weakly interconnected._
- **Should `require` be split into smaller, more focused modules?**
  _Cohesion score 0.0425531914893617 - nodes in this community are weakly interconnected._