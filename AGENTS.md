# OpenCode Project Configuration

## Project Overview

Symfony 7.1 REST API for book management using PHP 8.3.36, MariaDB 10, Nginx, and Docker.
Architecture: Hexagonal (Ports & Adapters) with strict separation of concerns.

**Stack:** PHP 8.3.36 | Symfony 7.1 | MariaDB 10 | Nginx | Docker
**PHP Version:** 8.3.36 (emergency security patch, vulnerability level 9 fixed)
**Analysis:** PHPStan Level 7 (strict)
**Style:** PHP-CS-Fixer (PSR-12 + Symfony custom)

## Default Agent

When no agent is specified, use **hexagonal-architect** by default.

### hexagonal-architect (Default)

**File:** `.opencode/agents/hexagonal-architect.md`
**Role:** Expert in hexagonal architecture, SOLID principles, and design patterns for PHP/Symfony.
**Use for:** Architecture reviews, refactoring, designing new features maintaining clean architecture.

**Responsibilities:**
1. Verify layer separation — domain must not know about infrastructure
2. Validate patterns — confirm correct use of interfaces, adapters, DTOs
3. Apply SOLID — verify each principle in proposed code
4. Suggest improvements — identify refactoring opportunities toward hexagonal architecture

**Strict Rules:**
- NEVER allow Entity to use `EntityManager`, `Doctrine`, or infrastructure classes
- ALWAYS inject interfaces, never concrete service classes
- NEVER put business logic in Controllers
- ALWAYS use DTOs for input/output data
- ALWAYS validate data in DTO or Service
- PHPUnit tests — Use `InMemory` repository implementations in `tests/Infrastructure/Repository/`. NEVER use infrastructure elements (Doctrine, Symfony Kernel, etc.) in PHPUnit tests.

**References:**
- Skill: `.opencode/skills/hexagonal-architecture/SKILL.md`
- Skill: `.opencode/skills/design-patterns/SKILL.md`
- Vault: `obsidian-vault/001-Architecture.md`
- Vault: `obsidian-vault/002-Design-Decisions.md`
- Vault: `obsidian-vault/100-Patterns-Used.md`

## Available Agents

### code-quality

**File:** `.opencode/agents/code-quality.md`
**Role:** PHP code quality expert specializing in PHPStan Level 7 and PHP-CS-Fixer.
**Use for:** Pre-commit code review, fixing style violations, static analysis errors.

**Responsibilities:**
1. Execute PHPStan — analyze code statically and fix violations
2. Execute PHP-CS-Fixer — automatically fix code style
3. Validate typing — ensure all types are correctly declared
4. Fix imports — sort, remove unused imports
5. Verify structure — validate code follows project conventions

**Strict Rules:**
- NEVER allow code with PHPStan Level 7 violations in the repo
- ALWAYS run PHP-CS-Fixer before PHPStan (fix style first)
- NEVER ignore PHPStan violations without justification
- ALWAYS use PHP 8.3+ native types (typed properties, union types)
- ALWAYS sort imports alphabetically
- ALWAYS remove unused imports

**References:**
- Skill: `.opencode/skills/code-quality/SKILL.md`
- Config: `phpstan.neon`
- Config: `.php-cs-fixer.php`
- Hook: `.git/hooks/pre-commit`

## Available Skills

### php-symfony
**File:** `.opencode/skills/php-symfony/SKILL.md`
**Use when:** Writing PHP code, controllers, entities, services, or tests.
**Covers:** PHP 8.3+ modern features, Symfony 7.1 attributes, DTOs, validation, PHPUnit testing, Docker commands.

### hexagonal-architecture
**File:** `.opencode/skills/hexagonal-architecture/SKILL.md`
**Use when:** Designing architecture, creating services, entities, or refactoring for better separation of concerns.
**Covers:** Ports & Adapters pattern, domain layer, application layer, adapter layer, value objects, domain events.

### design-patterns
**File:** `.opencode/skills/design-patterns/SKILL.md`
**Use when:** Designing code, refactoring, or choosing the correct pattern for a problem.
**Covers:** SOLID principles, GoF patterns (Creational, Structural, Behavioral), Symfony-specific patterns.

### code-quality
**File:** `.opencode/skills/code-quality/SKILL.md`
**Use when:** Maintaining clean code, strict typing, and error-free code before commits.
**Covers:** PHPStan Level 7, PHP-CS-Fixer, pre-commit hook configuration and usage.

### obsidian-vault
**File:** `.opencode/skills/obsidian-vault/SKILL.md`
**Use when:** Managing project knowledge, creating architecture notes, saving decisions, or preserving context.
**Covers:** Vault structure, ADR format, knowledge graph integration with graphify.

## Graphify — Knowledge Graph

This project has a knowledge graph at `graphify-out/` with god nodes, community structure, and cross-file relationships.

### Usage Rules

- For codebase questions, first run `graphify query "<question>"` when `graphify-out/graph.json` exists.
- Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for focused concepts.
- These return a scoped subgraph, usually much smaller than `GRAPH_REPORT.md` or raw grep output.
- Dirty `graphify-out/` files are expected after hooks or incremental updates; dirty graph files are not a reason to skip graphify.
- If `graphify-out/wiki/index.md` exists, use it for broad navigation instead of raw source browsing.
- Read `graphify-out/GRAPH_REPORT.md` only for broad architecture review or when query/path/explain do not surface enough context.
- After modifying code, run `graphify update .` to keep the graph current (AST-only, no API cost).

### Commands

```bash
# Update graph after code changes
graphify update .
make graphify-update

# Query the graph for relevant context
graphify query "how is book validation implemented?"
make graphify-query "book validation"

# Find path between files
graphify path "src/Controller/BookController.php" "src/Repository/BookRepository.php"
make graphify-path "src/Controller/BookController.php" "src/Repository/BookRepository.php"

# Explain a concept
graphify explain "RateLimitingMiddleware"
make graphify-explain "RateLimitingMiddleware"

# View most connected nodes (architectural hubs)
graphify god-nodes --top 10
make graphify-god-nodes

# View affected files by a change
graphify affected "src/DTO/CreateBookDTO.php"
make graphify-affected "src/DTO/CreateBookDTO.php"

# View graph report
cat graphify-out/GRAPH_REPORT.md
make graphify-report
```

### Benefits

| Before | After |
|--------|-------|
| Send entire project to LLM | Only relevant context |
| 100k+ tokens per query | 1-5k tokens per query |
| Generic answers | Contextualized answers |
| Slow analysis | Instant search |

## Development Workflow

### Before Coding

1. Create architecture note in `obsidian-vault/`
2. Consult graphify for existing patterns: `graphify query "pattern name"`
3. Review relevant ADRs in vault

### During Development

1. Follow hexagonal architecture rules
2. Update vault notes with decisions and patterns used
3. Run `graphify update .` after code changes

### Mandadory Dual-Agent Validation Gate

Before ANY commit, the code MUST pass validation from BOTH agents. This is a hard gate — no exceptions.

**Process:**
1. After writing or modifying code, launch BOTH agents in parallel:
   - `hexagonal-architect` — review architecture, SOLID, layer separation, dependency inversion, ports/adapters, DTOs
   - `code-quality` — run PHPStan Level 7, PHP-CS-Fixer, validate typing, fix imports
2. If EITHER agent reports violations:
   - Fix ALL reported issues
   - Re-run BOTH agents
   - Repeat until BOTH agents pass with zero violations
3. Only when BOTH agents report clean results can you proceed to commit

**Strict Rules:**
- NEVER commit code that fails either agent's review
- NEVER skip agent validation, even under time pressure
- NEVER ignore violations "with low priority" — all violations must be fixed
- If an agent reports a false positive, document the justification in a comment AND get agent confirmation before skipping
- Both agents must review ALL modified files, not just new ones
- Run `make quality` (PHPStan + CS-Fixer) inside Docker as a preliminary check, but agent review is still mandatory
- After fixing issues, always re-run both agents to ensure no regressions were introduced

**Common violation patterns that agents catch:**
- Domain layer depending on Infrastructure (EntityManager, Doctrine)
- Controllers with business logic
- Injecting concrete classes instead of interfaces
- Missing type declarations (PHPStan Level 7)
- Static mutable state in services
- SRP violations (controllers with multiple unrelated responsibilities)
- Memory issues (loading all records for aggregation)
- Incomplete repository implementations (missing interface methods)

### Before Committing

1. Run both agents — hexagonal-architect AND code-quality
2. Fix ALL violations from both agents
3. Re-run both agents until both pass clean
4. Fix code style: `make cs-fixer`
5. Fix static analysis: `make phpstan`
6. Verify pre-commit hook passes (runs automatically)
7. Update graph: `graphify update .`

## Docker Commands

```bash
# Start services
make up
make dev          # build + up

# Stop services
make down

# Restart
make restart

# Rebuild images
make build

# Clean containers and volumes
make clean

# View logs
make logs

# Shell inside PHP container
make shell

# Run Symfony commands
make symfony list:routes
make symfony cache:clear

# Run Composer commands
make composer install
make vendor       # shortcut for composer install

# Run PHP
make php --version

# Database operations
make db-migrate   # apply migrations
make db-reset     # drop and recreate schema
make db-dump      # export to SQL

# Testing
make test         # run PHPUnit tests

# Code quality
make phpstan      # static analysis
make cs-fixer     # fix code style
make cs-fixer-dry # verify without fixing
make quality      # run PHPStan + CS-Fixer
```

## API Endpoints

```
GET    http://localhost:8080/api/books      - List books
GET    http://localhost:8080/api/books/{id} - Show book
POST   http://localhost:8080/api/books      - Create book
PUT    http://localhost:8080/api/books/{id} - Update book
DELETE http://localhost:8080/api/books/{id} - Delete book
```

## Project Structure

```
src/
├── Controller/          # HTTP adapters (input)
├── Domain/              # Domain layer (entities, value objects)
├── Application/         # Application layer (services, DTOs, ports)
├── Infrastructure/      # Infrastructure adapters (repositories, middleware)
└── Adapter/             # External adapters

tests/
├── Application/         # Service and DTO tests
└── Infrastructure/      # Repository integration tests

obsidian-vault/          # Project knowledge base
graphify-out/            # Knowledge graph (auto-generated)
```

## Code Quality Gates

### PHPStan Level 7

**Config:** `phpstan.neon`
**Covers:** Typed parameters, typed returns, null access, method calls, array keys, type compatibility.

### PHP-CS-Fixer

**Config:** `.php-cs-fixer.php`
**Rules:** PSR-12 + Symfony custom (unused imports, array syntax, quotes, whitespace, visibility)

### Pre-commit Hook

**Location:** `.git/hooks/pre-commit`
**Runs inside Docker container:**
1. PHP-CS-Fixer (dry-run)
2. PHPStan (analyse)

**Blocks commit if any check fails.**

**Skip hook (NOT recommended):**
```bash
git commit --no-verify -m "message"
```

## Observations

- Project is partially refactored to hexagonal architecture
- Docker: php:8.3.36-fpm, MariaDB 10, Nginx
- PHPStan detected and fixed: missing types, null access, nonexistent methods, wrong imports
- PHP-CS-Fixer corrected: unused imports, alphabetical order, array syntax, quotes, whitespace
- Pre-commit hook executes inside Docker container via `docker compose exec`
- Graphify installed globally via `uv tool install`, integrated with OpenCode plugin
- OpenCode plugin `.opencode/plugins/graphify.js` runs before bash tool calls to suggest graphify queries
