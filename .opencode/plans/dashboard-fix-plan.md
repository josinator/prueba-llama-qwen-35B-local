# Plan: Fix Dashboard Buttons & Prevent Future API Contract Issues

## Problem Analysis

The dashboard buttons aren't working because of **API contract mismatches** between frontend (JavaScript) and backend (PHP controllers):

| Issue | JavaScript Expects | API Provides |
|-------|-------------------|--------------|
| Books list endpoint | `GET /dashboard/api/books` | Only `POST /dashboard/api/books` exists |
| Search parameter | `search=query` | Controller expects `q=query` |
| Response field | `data.items` | Controller returns `data.books` |
| Pagination field | `data.currentPage` | Controller returns `data.page` |

## Files Affected

- `assets/js/app.js` - JavaScript frontend code
- `src/Controller/DashboardController.php` - Backend API controller
- `tests/Integration/DashboardControllerTest.php` - Integration tests (to create)
- `tests/E2E/DashboardE2ETest.php` - E2E tests (to create)
- `.opencode/skills/frontend-symfony/SKILL.md` - Frontend skill (to update)
- `.opencode/AGENTS.md` - Agent configuration (to update)

## Phase 1: Fix JavaScript to Match API

**File:** `assets/js/app.js`

### 1.1 Fix loadBooks function (lines ~195-210)

**Current code:**
```javascript
async loadBooks(page = 1) {
    try {
        const url = this.searchQuery
            ? `/dashboard/api/books?page=${page}&search=${encodeURIComponent(this.searchQuery)}`
            : `/dashboard/api/books?page=${page}`;
        const data = await API.get(url);
        this.books = data.items || [];
        this.currentPage = data.currentPage || 1;
        this.totalPages = data.totalPages || 1;
        this.lastUpdated = new Date().toLocaleString();
    } catch (error) {
        console.error('Failed to load books:', error);
        this.showNotification('Failed to load books', 'error');
    }
}
```

**Fixed code:**
```javascript
async loadBooks(page = 1) {
    try {
        const url = this.searchQuery
            ? `/dashboard/api/search?q=${encodeURIComponent(this.searchQuery)}&page=${page}&limit=20`
            : `/dashboard/api/search?page=${page}&limit=20`;
        const data = await API.get(url);
        this.books = data.books || [];
        this.currentPage = data.page || 1;
        this.totalPages = data.totalPages || 1;
        this.lastUpdated = new Date().toLocaleString();
    } catch (error) {
        console.error('Failed to load books:', error);
        this.showNotification('Failed to load books', 'error');
    }
}
```

**Changes:**
- Endpoint: `/dashboard/api/books` → `/dashboard/api/search`
- Parameter: `search=` → `q=`
- Response field: `data.items` → `data.books`
- Pagination field: `data.currentPage` → `data.page`

### 1.2 Verify API methods work correctly

Test each endpoint manually:
```bash
# Stats
curl http://localhost:8080/dashboard/api/stats

# Search
curl "http://localhost:8080/dashboard/api/search?q=test&page=1&limit=20"

# Create
curl -X POST http://localhost:8080/dashboard/api/books \
  -H "Content-Type: application/json" \
  -d '{"title":"Test","author":"Test Author","description":"Test description"}'

# Update
curl -X PUT http://localhost:8080/dashboard/api/books/{id} \
  -H "Content-Type: application/json" \
  -d '{"title":"Updated Title","author":"Updated Author","description":"Updated description"}'

# Delete
curl -X DELETE http://localhost:8080/dashboard/api/books/{id}

# Show
curl http://localhost:8080/dashboard/api/books/{id}
```

## Phase 2: Add Integration Tests

**File:** `tests/Integration/DashboardControllerTest.php`

### 2.1 Create test class

```php
<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Controller\DashboardController;
use App\Application\BookApplicationServiceInterface;
use App\Application\BookStatisticsServiceInterface;
use App\Application\Port\BookChangeTrackerInterface;
use App\Application\Port\BookSearchServiceInterface;
use App\Domain\Entity\Book;
use App\Domain\ValueObject\Title;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardControllerTest extends WebTestCase
{
    private \HttpClient $client;
    private BookApplicationServiceInterface $bookService;
    private BookStatisticsServiceInterface $statisticsService;
    private BookSearchServiceInterface $bookSearchService;
    private BookChangeTrackerInterface $changeTracker;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        // TODO: Mock services or use test database
    }

    public function testStatsEndpointReturnsCorrectFormat(): void
    {
        $this->client->request('GET', '/dashboard/api/stats');

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertResponseIsSame(200);

        $data = json_decode($response->getContent(), true);

        // Verify response format matches JavaScript expectations
        $this->assertArrayHasKey('totalBooks', $data);
        $this->assertArrayHasKey('booksWithDescription', $data);
        $this->assertArrayHasKey('booksWithoutDescription', $data);
        $this->assertArrayHasKey('createdToday', $data);
        $this->assertArrayHasKey('updatedToday', $data);
        $this->assertArrayHasKey('authorsCount', $data);
        $this->assertArrayHasKey('oldestBookTitle', $data);
        $this->assertArrayHasKey('newestBookTitle', $data);
    }

    public function testSearchEndpointReturnsCorrectFormat(): void
    {
        $this->client->request('GET', '/dashboard/api/search?q=test&page=1&limit=20');

        $response = $this->client->getResponse();
        $this->assertResponseIsSame(200);

        $data = json_decode($response->getContent(), true);

        // Verify response format matches JavaScript expectations
        $this->assertArrayHasKey('books', $data);
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('page', $data);
        $this->assertArrayHasKey('limit', $data);
        $this->assertArrayHasKey('totalPages', $data);

        // Verify books array structure
        if (count($data['books']) > 0) {
            $book = $data['books'][0];
            $this->assertArrayHasKey('id', $book);
            $this->assertArrayHasKey('title', $book);
            $this->assertArrayHasKey('author', $book);
            $this->assertArrayHasKey('description', $book);
            $this->assertArrayHasKey('createdAt', $book);
            $this->assertArrayHasKey('updatedAt', $book);
        }
    }

    public function testCreateBookEndpointReturnsCorrectFormat(): void
    {
        $this->client->request(
            'POST',
            '/dashboard/api/books',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'title' => 'Test Book',
                'author' => 'Test Author',
                'description' => 'Test description',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertResponseIsSame(201);

        $data = json_decode($response->getContent(), true);

        // Verify response format matches JavaScript expectations
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('author', $data);
        $this->assertArrayHasKey('description', $data);
        $this->assertArrayHasKey('createdAt', $data);
        $this->assertArrayHasKey('updatedAt', $data);
    }

    public function testUpdateBookEndpointReturnsCorrectFormat(): void
    {
        // TODO: Create a book first, then update it
        // $bookId = ...;

        $this->client->request(
            'PUT',
            '/dashboard/api/books/{id}',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'title' => 'Updated Title',
                'author' => 'Updated Author',
                'description' => 'Updated description',
            ])
        );

        $response = $this->client->getResponse();
        $this->assertResponseIsSame(200);

        $data = json_decode($response->getContent(), true);

        // Verify response format matches JavaScript expectations
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('author', $data);
        $this->assertArrayHasKey('description', $data);
        $this->assertArrayHasKey('createdAt', $data);
        $this->assertArrayHasKey('updatedAt', $data);
    }

    public function testDeleteBookEndpointReturnsCorrectFormat(): void
    {
        // TODO: Create a book first, then delete it
        // $bookId = ...;

        $this->client->request('DELETE', '/dashboard/api/books/{id}');

        $response = $this->client->getResponse();
        $this->assertResponseIsSame(200);

        $data = json_decode($response->getContent(), true);

        // Verify response format matches JavaScript expectations
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
    }

    public function testShowBookEndpointReturnsCorrectFormat(): void
    {
        // TODO: Create a book first, then show it
        // $bookId = ...;

        $this->client->request('GET', '/dashboard/api/books/{id}');

        $response = $this->client->getResponse();
        $this->assertResponseIsSame(200);

        $data = json_decode($response->getContent(), true);

        // Verify response format matches JavaScript expectations
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('author', $data);
        $this->assertArrayHasKey('description', $data);
        $this->assertArrayHasKey('createdAt', $data);
        $this->assertArrayHasKey('updatedAt', $data);
    }
}
```

### 2.2 Run tests

```bash
make test
```

## Phase 3: Add E2E Tests

**File:** `tests/E2E/DashboardE2ETest.php`

### 3.1 Create test class

```php
<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DashboardE2ETest extends WebTestCase
{
    public function testDashboardPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/dashboard');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('html');
        $this->assertSelectorExists('[x-data="dashboard"]');
    }

    public function testSearchFunctionality(): void
    {
        $client = static::createClient();
        $client->request('GET', '/dashboard');

        // TODO: Use Playwright or Selenium for browser automation
        // This is a placeholder for E2E tests
    }

    public function testCreateBookModal(): void
    {
        $client = static::createClient();
        $client->request('GET', '/dashboard');

        // TODO: Use Playwright or Selenium for browser automation
        // This is a placeholder for E2E tests
    }

    public function testEditBookModal(): void
    {
        $client = static::createClient();
        $client->request('GET', '/dashboard');

        // TODO: Use Playwright or Selenium for browser automation
        // This is a placeholder for E2E tests
    }

    public function testDeleteBookModal(): void
    {
        $client = static::createClient();
        $client->request('GET', '/dashboard');

        // TODO: Use Playwright or Selenium for browser automation
        // This is a placeholder for E2E tests
    }

    public function testPagination(): void
    {
        $client = static::createClient();
        $client->request('GET', '/dashboard');

        // TODO: Use Playwright or Selenium for browser automation
        // This is a placeholder for E2E tests
    }

    public function testSSEConnection(): void
    {
        $client = static::createClient();
        $client->request('GET', '/dashboard/sse');

        // TODO: Verify SSE connection establishes
        // This is a placeholder for E2E tests
    }
}
```

### 3.2 Install Playwright (optional)

```bash
composer require --dev playwright/php-playwright
npx playwright install
```

## Phase 4: Update Frontend Skill

**File:** `.opencode/skills/frontend-symfony/SKILL.md`

### 4.1 Add new section: "API Contract Verification"

```markdown
### API Contract Verification

**CRITICAL:** Before implementing any frontend feature that interacts with the API:

1. **Verify endpoint exists** - Check `debug:router` to confirm the route exists
2. **Check HTTP method** - Verify the method (GET, POST, PUT, DELETE) matches
3. **Check parameters** - Verify query parameters and body format
4. **Check response format** - Verify the JSON structure matches what the frontend expects
5. **Write integration tests** - Create tests that verify the API response format
6. **Write E2E tests** - Create tests that verify the full-stack functionality

**Common mistakes to avoid:**
- Using wrong endpoint (e.g., `/api/books` vs `/dashboard/api/books`)
- Using wrong parameter name (e.g., `search` vs `q`)
- Expecting wrong response field (e.g., `items` vs `books`)
- Missing pagination fields (e.g., `currentPage` vs `page`)

**Verification checklist:**
- [ ] Route exists in `debug:router`
- [ ] HTTP method matches
- [ ] Parameters match
- [ ] Response format matches
- [ ] Integration tests pass
- [ ] E2E tests pass
```

## Phase 5: Update AGENTS.md

**File:** `.opencode/AGENTS.md`

### 5.1 Update "Mandatory Dual-Agent Validation Gate"

Add to frontend validation process:

```markdown
**Process for frontend changes (Twig, CSS, JS, HTML, templates):**
1. After writing or modifying frontend code, launch BOTH agents in parallel:
   - `frontend` — review Twig templates, Alpine.js components, CSS, modals, dashboards, SSE
   - `hexagonal-architect` — verify no business logic in templates, proper separation of concerns
2. **API Contract Verification:**
   - Verify all API endpoints exist using `debug:router`
   - Check request/response format matches between frontend and backend
   - Run integration tests to verify API contract
   - Run E2E tests to verify full-stack functionality
3. If ANY agent reports violations:
   - Fix ALL reported issues
   - Re-run ALL agents
   - Repeat until ALL agents pass with zero violations
4. Only when ALL agents report clean results can you proceed to commit
```

## Execution Order

1. **Phase 1** - Fix JavaScript to match API (5 minutes)
2. **Phase 2** - Add integration tests (15 minutes)
3. **Phase 3** - Add E2E tests (20 minutes)
4. **Phase 4** - Update frontend skill (10 minutes)
5. **Phase 5** - Update AGENTS.md (5 minutes)

**Total: ~55 minutes**

## Verification

After implementation, verify the dashboard works:

1. Open `http://localhost:8080/dashboard`
2. Test search functionality
3. Test create book modal
4. Test edit book modal
5. Test delete book modal
6. Test pagination
7. Run `make test` to verify all tests pass
8. Run `make quality` to verify code quality

## Notes

- The dashboard is served via Nginx at `http://localhost:8080/dashboard`
- API endpoints are under `/dashboard/api/`
- Assets are served from `/styles/` and `/js/`
- SSE endpoint is at `/dashboard/sse`
- Alpine.js is loaded from CDN at `https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js`
