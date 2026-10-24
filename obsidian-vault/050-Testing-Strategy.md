---
type: strategy
tags: [testing, behat, playwright, e2e]
---

# Testing Strategy

## Capas de Testing

### 1. PHPUnit — Unit Tests
- **Dónde:** `tests/Application/`, `tests/Infrastructure/`
- **Qué:** Servicios, DTOs, Value Objects, Repositorios InMemory
- **Regla:** NO usar infraestructura real (Doctrine, Symfony Kernel) en tests unitarios
- **Ejecución:** `make test`

### 2. Behat — API BDD Tests
- **Dónde:** `features/api/*.feature` + `tests/Behat/Context/`
- **Qué:** Contratos de API REST y Dashboard API
- **Cobertura:**
  - CRUD de libros (`/api/books`)
  - Stats (`/dashboard/api/stats`)
  - Búsqueda (`/dashboard/api/search`)
  - Error handling (400, 404, 422)
- **Ejecución:** `make behat`

### 3. Playwright — E2E Tests
- **Dónde:** `tests/E2E/*.spec.ts`
- **Qué:** Flujo completo del Dashboard UI
- **Cobertura:**
  - Stats display
  - Create/Edit/Delete via modales
  - SSE real-time updates
  - Search con debounce
- **Ejecución:** `make playwright`

### 4. Full Stack
- **Ejecución:** `make test-all` (PHPUnit + Behat + Playwright)

## Herramientas

| Herramienta | Tipo | Propósito |
|-------------|------|-----------|
| PHPUnit 11.5 | Unit/Integration | Tests de código PHP |
| Behat 4.0 | BDD/API | Tests de contratos API |
| Playwright | E2E | Tests de interfaz de usuario |

## data-testid

Todos los elementos interactivos del Dashboard tienen `data-testid` para selección estable en Playwright:

- `stats-grid`, `stat-totalBooks`, `stat-createdToday`, etc.
- `search-input`, `search-button`, `new-book-button`
- `books-table`, `books-tbody`, `book-row-{id}`, `edit-button`, `delete-button`
- `modal-overlay`, `modal`, `modal-title-input`, `modal-author-input`, `modal-save`, `modal-cancel`
- `delete-modal-overlay`, `delete-modal`, `delete-cancel`, `delete-confirm`
- `notification-container`, `sse-status`, `pagination`

## Makefile Targets

```bash
make test        # PHPUnit
make behat       # Behat
make playwright  # Playwright
make test-all    # Todos los tests
make quality     # PHPStan + CS-Fixer
```
