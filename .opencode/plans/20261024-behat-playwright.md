# Plan: Behat + Playwright Testing Implementation

## Contexto Minimal del Proyecto

**Stack:** Symfony 7.1, PHP 8.3.36, MariaDB 10.11, Nginx, Alpine.js 3.14.3
**Arquitectura:** Hexagonal (Ports & Adapters)
**Docker:** PHP FPM 8.3, MariaDB 10.11, Nginx en puertos 8080/9000/3307
**Base de datos:** `books`, usuario `symfony/symfony`, puerto 3307
**API Base:** `http://localhost:8080`

**Endpoints existentes:**
- `GET/POST /api/books` — BookController (list/create)
- `GET/PUT/DELETE /api/books/{id}` — BookController (show/update/delete)
- `GET /dashboard` — DashboardController (render Twig)
- `GET /dashboard/api/stats` — Devuelve stats JSON
- `GET /dashboard/api/search?q=&page=&limit=` — Devuelve libros paginados
- `POST/PUT/DELETE /dashboard/api/books/{id}` — CRUD via Dashboard
- `GET /dashboard/sse` — Server-Sent Events (book.created/updated/deleted)

**Dashboard UI (Alpine.js):**
- Stats cards (totalBooks, createdToday, updatedToday, booksWithDescription, booksWithoutDescription)
- Tabla de libros con paginación
- Búsqueda con debounce 300ms
- Modales create/edit/delete
- Toast notifications (auto-dismiss 3s)
- SSE status indicator (connected/disconnected)
- Alpine component: `x-data="dashboard"`

**DTOs existentes:**
- `CreateBookDTO` — fields: title (required), author (required), description (optional)
- `UpdateBookDTO` — fields: title (optional), author (optional), description (optional)
- `SearchBooksDTO` — fields: query (string), page (int), limit (int)
- Métodos: `fromArray()`, `fromRequest()`, `validate()`

**Archivos de test a eliminar:**
- `tests/Integration/DashboardControllerTest.php` (14 tests)
- `tests/E2E/DashboardE2ETest.php` (8 tests)
- `tests/Integration/SimpleDebugTest.php`
- `tests/Integration/DebugBookTest.php`

---

## Fase 0 — Setup & Configuration

**Contexto necesario:** Solo esta sección + estructura de directorios

### 0.1 Instalar dependencias

**Composer (dentro de contenedor):**
```bash
docker compose exec app composer require --dev behat/behat symfony/http-client
```

**npm (fuera de contenedor):**
```bash
npm init -y
npm install --save-dev playwright
npx playwright install chromium
```

### 0.2 Crear archivos de configuración

**`behat.yml`:**
```yaml
behat:
  suites:
    api:
      paths: [features/api]
      contexts:
        - App\Tests\Behat\Context\ApiContext
        - App\Tests\Behat\Context\BookContext
```

**`playwright.config.ts`:**
```typescript
import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: 'tests/E2E',
  testMatch: '**/*.spec.ts',
  fullyParallel: true,
  retries: 1,
  workers: 1,
  use: {
    baseURL: 'http://localhost:8080',
    chromiumSandbox: false,
    trace: 'on-first-retry',
  },
  webServer: {
    command: 'docker compose up -d',
    url: 'http://localhost:8080',
    reuseExistingServer: true,
    timeout: 60000,
    stdout: 'pipe',
  },
});
```

### 0.3 Crear directorios

```bash
mkdir -p features/api tests/Behat/Context
```

### Done Criteria Fase 0
- [ ] `behat` comando disponible en vendor/bin/behat
- [ ] `@playwright/test` instalado en node_modules
- [ ] Chromium instalado para Playwright
- [ ] `behat.yml` existe en raíz del proyecto
- [ ] `playwright.config.ts` existe en raíz del proyecto
- [ ] Directorios `features/api/` y `tests/Behat/Context/` creados
- [ ] `composer.json` incluye `behat/behat` en require-dev
- [ ] `package.json` incluye `@playwright/test` en devDependencies

---

## Fase 1 — Behat API Tests

**Contexto necesario:** Solo esta sección + Fase 0 done criteria

### 1.1 Crear KernelBootTrait

**`tests/Behat/KernelBootTrait.php`:**
```php
<?php
declare(strict_types=1);

namespace App\Tests\Behat;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

trait KernelBootTrait
{
    private static ?KernelInterface $kernel = null;
    private ?KernelBrowser $client = null;

    private function bootClient(): KernelBrowser
    {
        if (null === $this->client) {
            if (null === self::$kernel) {
                self::$kernel = (new \App\Kernel())->boot();
            }
            $this->client = new KernelBrowser(self::$kernel);
        }
        return $this->client;
    }

    private function getContainer(): ContainerInterface
    {
        return self::$kernel->getContainer();
    }
}
```

### 1.2 Crear ApiContext

**`tests/Behat/Context/ApiContext.php`:**
```php
<?php
declare(strict_types=1);

namespace App\Tests\Behat\Context;

use App\Tests\Behat\KernelBootTrait;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\HttpFoundation\Response;
use PHPUnit\Framework\Assert;

class ApiContext extends Assert
{
    use KernelBootTrait;

    private array $response = [];
    private int $statusCode = 0;
    private string $baseUrl = 'http://localhost:8080';

    public function setBaseUrl(string $url): void
    {
        $this->baseUrl = $url;
    }

    public function sendRequest(string $method, string $path, ?string $body = null): void
    {
        $client = $this->bootClient();
        $requestHeaders = ['CONTENT_TYPE' => 'application/json'];

        $client->request($method, $this->baseUrl . $path, [], [], $requestHeaders, $body ?? '');

        $this->statusCode = $client->getResponse()->getStatusCode();
        $content = $client->getResponse()->getContent();

        try {
            $this->response = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->response = ['raw' => $content];
        }
    }

    public function assertStatusCode(int $expected): void
    {
        $this->assertEquals($expected, $this->statusCode);
    }

    public function assertResponseHasKey(string $key): void
    {
        $this->assertArrayHasKey($key, $this->response);
    }

    public function assertResponseHasKeyWithValue(string $key, $value): void
    {
        $this->assertArrayHasKey($key, $this->response);
        $this->assertEquals($value, $this->response[$key]);
    }

    public function assertResponseIsArray(): void
    {
        $this->assertIsArray($this->response);
    }

    public function assertResponseContains(string $needle): void
    {
        $content = json_encode($this->response);
        $this->assertStringContainsString($needle, $content);
    }

    public function assertJsonResponseStructure(array $structure): void
    {
        foreach ($structure as $key => $type) {
            $this->assertArrayHasKey($key, $this->response);
            if ($type === 'array') {
                $this->assertIsArray($this->response[$key]);
            } elseif ($type === 'int') {
                $this->assertIsInt($this->response[$key]);
            } elseif ($type === 'string') {
                $this->assertIsString($this->response[$key]);
            } elseif ($type === 'bool') {
                $this->assertIsBool($this->response[$key]);
            }
        }
    }

    public function getResponseValue(string $key)
    {
        return $this->response[$key] ?? null;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
```

### 1.3 Crear BookContext

**`tests/Behat/Context/BookContext.php`:**
```php
<?php
declare(strict_types=1);

namespace App\Tests\Behat\Context;

use Behat\Gherkin\Node\PyStringNode;

class BookContext extends ApiContext
{
    private ?string $lastCreatedBookId = null;

    public function iCreateABookWithFields(PyStringNode $json): void
    {
        $this->sendRequest('POST', '/api/books', $json->getRaw());
    }

    public function iUpdateBookWithFields(string $id, PyStringNode $json): void
    {
        $this->sendRequest('PUT', "/api/books/{$id}", $json->getRaw());
    }

    public function iDeleteBook(string $id): void
    {
        $this->sendRequest('DELETE', "/api/books/{$id}");
    }

    public function iGetBook(string $id): void
    {
        $this->sendRequest('GET', "/api/books/{$id}");
    }

    public function iSearchBooksWithQuery(string $query): void
    {
        $this->sendRequest('GET', "/dashboard/api/search?q={$query}&page=1&limit=20");
    }

    public function iRequestStats(): void
    {
        $this->sendRequest('GET', '/dashboard/api/stats');
    }

    public function storeCreatedBookId(): void
    {
        if (isset($this->response['id'])) {
            $this->lastCreatedBookId = $this->response['id'];
        }
    }

    public function assertBookStructure(): void
    {
        $this->assertResponseHasKey('id');
        $this->assertResponseHasKey('title');
        $this->assertResponseHasKey('author');
        $this->assertResponseHasKey('description');
        $this->assertResponseHasKey('createdAt');
        $this->assertResponseHasKey('updatedAt');
    }

    public function getLastCreatedBookId(): ?string
    {
        return $this->lastCreatedBookId;
    }
}
```

### 1.4 Crear feature files

**`features/api/book.feature`:**
```gherkin
Feature: Book CRUD Operations
  As a book manager
  I want to create, read, update, and delete books

  Scenario: Create a new book
    When I send a POST request to "/api/books" with body:
      """
      {
        "title": "Behat Test Book",
        "author": "Test Author",
        "description": "Created via Behat"
      }
      """
    Then the status code should be 201
    And the response should have key "id"
    And the response should have key "title" with value "Behat Test Book"

  Scenario: Create book with missing title returns 422
    When I send a POST request to "/api/books" with body:
      """
      {
        "author": "Test Author"
      }
      """
    Then the status code should be 422
    And the response should have key "violations"

  Scenario: Create book with invalid JSON returns 400
    When I send a POST request to "/api/books" with body:
      """
      not valid json
      """
    Then the status code should be 400

  Scenario: Show a book by ID
    When I send a POST request to "/api/books" with body:
      """
      {
        "title": "Show Test",
        "author": "Show Author",
        "description": "For show test"
      }
      """
    Then the status code should be 201
    When I send a GET request to "/api/books/1"
    Then the status code should be 200

  Scenario: Show non-existent book returns 404
    When I send a GET request to "/api/books/00000000-0000-0000-0000-000000000000"
    Then the status code should be 404

  Scenario: Update a book
    When I send a POST request to "/api/books" with body:
      """
      {
        "title": "Original Title",
        "author": "Original Author"
      }
      """
    Then the status code should be 201
    When I send a PUT request to "/api/books/1" with body:
      """
      {
        "title": "Updated Title"
      }
      """
    Then the status code should be 200
    And the response should have key "title" with value "Updated Title"

  Scenario: Update non-existent book returns 404
    When I send a PUT request to "/api/books/00000000-0000-0000-0000-000000000000" with body:
      """
      {
        "title": "Updated"
      }
      """
    Then the status code should be 404

  Scenario: Delete a book
    When I send a POST request to "/api/books" with body:
      """
      {
        "title": "Delete Me",
        "author": "Delete Author"
      }
      """
    Then the status code should be 201
    When I send a DELETE request to "/api/books/1"
    Then the status code should be 200
    And the response should have key "success" with value true

  Scenario: Delete non-existent book returns 404
    When I send a DELETE request to "/api/books/00000000-0000-0000-0000-000000000000"
    Then the status code should be 404
```

**`features/api/dashboard_api.feature`:**
```gherkin
Feature: Dashboard API Endpoints
  As a dashboard user
  I want to access stats, search, and book management APIs

  Scenario: Get statistics
    When I send a GET request to "/dashboard/api/stats"
    Then the status code should be 200
    And the response should have key "totalBooks"
    And the response should have key "booksWithDescription"
    And the response should have key "createdToday"
    And the response should have key "updatedToday"
    And the response should have key "authorsCount"

  Scenario: Search books with query
    When I send a GET request to "/dashboard/api/search?q=test&page=1&limit=20"
    Then the status code should be 200
    And the response should have key "books"
    And the response should have key "total"
    And the response should have key "page"
    And the response should have key "totalPages"

  Scenario: Search books with empty query
    When I send a GET request to "/dashboard/api/search?page=1&limit=20"
    Then the status code should be 200
    And the response should have key "books"

  Scenario: Dashboard index page loads
    When I send a GET request to "/dashboard"
    Then the status code should be 200
```

**`features/api/error_handling.feature`:**
```gherkin
Feature: Error Handling
  As an API consumer
  I want to receive proper error responses

  Scenario: Invalid JSON on create returns 400
    When I send a POST request to "/api/books" with body:
      """
      {invalid json}
      """
    Then the status code should be 400

  Scenario: Invalid JSON on update returns 400
    When I send a PUT request to "/api/books/1" with body:
      """
      {invalid json}
      """
    Then the status code should be 400

  Scenario: Missing required fields on create returns 422
    When I send a POST request to "/api/books" with body:
      """
      {}
      """
    Then the status code should be 422
    And the response should have key "violations"
```

### Done Criteria Fase 1
- [ ] `tests/Behat/KernelBootTrait.php` creado y funcional
- [ ] `tests/Behat/Context/ApiContext.php` creado con métodos de assertion
- [ ] `tests/Behat/Context/BookContext.php` creado extendiendo ApiContext
- [ ] `features/api/book.feature` creado con 9 escenarios CRUD
- [ ] `features/api/dashboard_api.feature` creado con 4 escenarios
- [ ] `features/api/error_handling.feature` creado con 4 escenarios
- [ ] `behat` ejecuta todos los escenarios sin errores
- [ ] `make behat` target añadido a Makefile

---

## Fase 2 — Playwright E2E Tests

**Contexto necesario:** Solo esta sección + Fase 0 done criteria

### 2.1 Añadir data-testid al template

**`templates/dashboard/index.html.twig`** — Añadir atributos `data-testid` a elementos clave:

```twig
<!-- Stats grid -->
<div class="stats-grid" id="stats-grid" data-testid="stats-grid">
    <div class="stat-card" data-testid="stat-totalBooks">
        <div class="value" x-text="stats.totalBooks"></div>
        <div class="label">Total Books</div>
    </div>
    <!-- ... repetir para cada stat ... -->
</div>

<!-- Search -->
<input type="text" data-testid="search-input" x-model="searchQuery" @input.debounce.300ms="searchBooks()" placeholder="Search...">
<button data-testid="search-button" @click="searchBooks()">Search</button>
<button class="btn btn-success" data-testid="new-book-button" @click="openCreateModal()">+ New Book</button>

<!-- Table -->
<table data-testid="books-table">
    <tbody id="books-tbody" data-testid="books-tbody">
        <!-- Cada row: data-testid="book-row-{{ book.id }}" -->
    </tbody>
</table>

<!-- Pagination -->
<div class="pagination" id="pagination" data-testid="pagination"></div>

<!-- SSE Status -->
<div class="sse-status" data-testid="sse-status" aria-live="polite"></div>

<!-- Create/Edit Modal -->
<div class="modal-overlay" x-show="modal.show" x-cloak data-testid="modal-overlay">
    <div class="modal" data-testid="modal">
        <h3 data-testid="modal-title" x-text="modal.mode === 'create' ? 'Create New Book' : 'Edit Book'"></h3>
        <form @submit.prevent="handleSubmit()">
            <input type="text" data-testid="modal-title-input" x-model="modal.title" required>
            <input type="text" data-testid="modal-author-input" x-model="modal.author" required>
            <textarea data-testid="modal-description-input" x-model="modal.description"></textarea>
            <button type="button" data-testid="modal-cancel" @click="closeModal()">Cancel</button>
            <button type="submit" data-testid="modal-save">Save</button>
        </form>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal-overlay" x-show="deleteModal.show" x-cloak data-testid="delete-modal-overlay">
    <div class="modal" data-testid="delete-modal">
        <button data-testid="delete-cancel" @click="closeDeleteModal()">Cancel</button>
        <button data-testid="delete-confirm" @click="deleteBook()">Delete</button>
    </div>
</div>

<!-- Notifications -->
<div class="notification-container" data-testid="notification-container" aria-live="polite"></div>
```

### 2.2 Crear specs de Playwright

**`tests/E2E/dashboard.spec.ts`:**
```typescript
import { test, expect } from '@playwright/test';

test.describe('Dashboard', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/dashboard');
    await page.waitForLoadState('networkidle');
  });

  test('stats are displayed', async ({ page }) => {
    await expect(page.getByTestId('stats-grid')).toBeVisible();
    await expect(page.getByTestId('stat-totalBooks')).toBeVisible();
  });

  test('search input is visible', async ({ page }) => {
    await expect(page.getByTestId('search-input')).toBeVisible();
  });

  test('new book button is visible', async ({ page }) => {
    await expect(page.getByTestId('new-book-button')).toBeVisible();
  });

  test('books table is visible', async ({ page }) => {
    await expect(page.getByTestId('books-table')).toBeVisible();
  });
});
```

**`tests/E2E/create-edit-delete.spec.ts`:**
```typescript
import { test, expect } from '@playwright/test';

test.describe('Create, Edit, Delete', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/dashboard');
    await page.waitForLoadState('networkidle');
  });

  test('create a new book via modal', async ({ page }) => {
    await page.getByTestId('new-book-button').click();
    await expect(page.getByTestId('modal-overlay')).toBeVisible();

    await page.getByTestId('modal-title-input').fill('Playwright Test Book');
    await page.getByTestId('modal-author-input').fill('E2E Author');
    await page.getByTestId('modal-save').click();

    await expect(page.getByTestId('modal-overlay')).not.toBeVisible();
  });

  test('delete a book via confirmation modal', async ({ page }) => {
    const book = await page.evaluate(async () => {
      const res = await fetch('/api/books', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title: 'Delete Test', author: 'Author', description: 'Test' })
      });
      return res.json();
    });

    await page.reload();
    await page.waitForLoadState('networkidle');

    await page.locator(`tr:has-text("${book.title}")`).getByTestId('delete-button').click();
    await expect(page.getByTestId('delete-modal-overlay')).toBeVisible();

    await page.getByTestId('delete-confirm').click();
    await expect(page.getByTestId('delete-modal-overlay')).not.toBeVisible();
  });
});
```

**`tests/E2E/sse-realtime.spec.ts`:**
```typescript
import { test, expect } from '@playwright/test';

test.describe('SSE Real-time Updates', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/dashboard');
    await page.waitForLoadState('networkidle');
  });

  test('SSE shows connected status', async ({ page }) => {
    await expect(page.getByTestId('sse-status')).toBeVisible();
  });

  test('creates book via API and sees real-time update', async ({ page }) => {
    const responsePromise = page.waitForResponse(
      r => r.url().includes('/dashboard/api/search') && r.status() === 200
    );

    await page.evaluate(async () => {
      await fetch('/api/books', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ title: 'SSE Test', author: 'Author', description: 'Test' })
      });
    });

    await responsePromise;
    await expect(page.getByTestId('books-tbody')).toContainText('SSE Test');
  });
});
```

**`tests/E2E/search.spec.ts`:**
```typescript
import { test, expect } from '@playwright/test';

test.describe('Search', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/dashboard');
    await page.waitForLoadState('networkidle');
  });

  test('search with debounce works', async ({ page }) => {
    const searchInput = page.getByTestId('search-input');
    await searchInput.fill('Test Book');
    await page.waitForResponse(
      r => r.url().includes('/dashboard/api/search') && r.status() === 200
    );
    await expect(page.getByTestId('search-button')).toBeVisible();
  });
});
```

### Done Criteria Fase 2
- [ ] `templates/dashboard/index.html.twig` tiene data-testid en todos los elementos clave
- [ ] `tests/E2E/dashboard.spec.ts` creado con 4 tests
- [ ] `tests/E2E/create-edit-delete.spec.ts` creado con 2 tests
- [ ] `tests/E2E/sse-realtime.spec.ts` creado con 2 tests
- [ ] `tests/E2E/search.spec.ts` creado con 1 test
- [ ] `npx playwright test` ejecuta todos los specs sin errores
- [ ] Playwright usa data-testid para seleccionar elementos

---

## Fase 3 — Cleanup & Integration

**Contexto necesario:** Solo esta sección + Fase 0-2 done criteria

### 3.1 Eliminar tests antiguos

```bash
rm tests/Integration/SimpleDebugTest.php
rm tests/Integration/DebugBookTest.php
rm tests/Integration/DashboardControllerTest.php
rm tests/E2E/DashboardE2ETest.php
```

### 3.2 Actualizar Makefile

Añadir targets:
```makefile
behat: ## Ejecuta Behat (API BDD tests)
	docker compose exec $(PHP_SERVICE) php ./vendor/bin/behat --colors --strict

playwright: ## Ejecuta Playwright (E2E tests)
	npx playwright test

test-all: ## Ejecuta PHPUnit + Behat + Playwright
	$(MAKE) test
	$(MAKE) behat
	$(MAKE) playwright

.PHONY: behat playwright test-all
```

### Done Criteria Fase 3
- [ ] Tests antiguos eliminados
- [ ] `make behat` ejecuta Behat correctamente
- [ ] `make playwright` ejecuta Playwright correctamente
- [ ] `make test-all` ejecuta PHPUnit + Behat + Playwright
- [ ] `make quality` sigue funcionando (PHPStan + CS-Fixer)
- [ ] `make test` sigue funcionando (PHPUnit)

---

## Resumen de Archivos

| Archivo | Acción | Fase |
|---------|--------|------|
| `behat.yml` | Crear | 0 |
| `playwright.config.ts` | Crear | 0 |
| `features/api/book.feature` | Crear | 1 |
| `features/api/dashboard_api.feature` | Crear | 1 |
| `features/api/error_handling.feature` | Crear | 1 |
| `tests/Behat/KernelBootTrait.php` | Crear | 1 |
| `tests/Behat/Context/ApiContext.php` | Crear | 1 |
| `tests/Behat/Context/BookContext.php` | Crear | 1 |
| `templates/dashboard/index.html.twig` | Modificar | 2 |
| `tests/E2E/dashboard.spec.ts` | Crear | 2 |
| `tests/E2E/create-edit-delete.spec.ts` | Crear | 2 |
| `tests/E2E/sse-realtime.spec.ts` | Crear | 2 |
| `tests/E2E/search.spec.ts` | Crear | 2 |
| `Makefile` | Modificar | 3 |
| `tests/Integration/DashboardControllerTest.php` | Eliminar | 3 |
| `tests/E2E/DashboardE2ETest.php` | Eliminar | 3 |
| `tests/Integration/SimpleDebugTest.php` | Eliminar | 3 |
| `tests/Integration/DebugBookTest.php` | Eliminar | 3 |
