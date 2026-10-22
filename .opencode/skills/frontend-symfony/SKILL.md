---
name: frontend-symfony
description: Guía experta de frontend para Symfony 7.1 + PHP 8.3. Cubre Twig templates, Symfony UX (Stimulus, Turbo, Volt), Alpine.js, HTMX, Vite, modales, dashboards, tablas interactivas, SSE, y patrones de UI server-driven. Usa cuando crees vistas, dashboards, modales, componentes UI, o cualquier interfaz de usuario que se integre con Symfony.
---

# Frontend para Symfony 7.1 — Skill de Desarrollo

## Filosofía

El frontend de este proyecto sigue un enfoque **server-driven** con opciones progresivas de interactividad. La prioridad es:

1. **Twig + contenido HTML** — Renderizado del lado del servidor, SEO-friendly, sin build step
2. **Alpine.js** — Interactividad ligera sin framework pesado
3. **Symfony UX** — Ecosistema oficial de Symfony para componentes interactivos
4. **HTMX** — Interacciones AJAX sin JavaScript boilerplate
5. **Vite** — Build moderno cuando se necesitan frameworks pesados (React/Vue/Svelte)

## Stack Frontend del Proyecto

```
Twig (Motor de plantillas)
├── Symfony UX Stimulus (Controladores JS reutilizables)
├── Alpine.js (Reactividad ligera)
├── HTMX (Interacciones server-driven)
├── Vite (Build moderno — opcional)
└── CSS custom / Tailwind (Estilos)
```

## Estructura de Archivos Frontend

```
assets/
├── controllers/           # Symfony UX Stimulus controllers
│   ├── hello_controller.js
│   └── ...
├── styles/
│   ├── app.css            # Estilos globales
│   ├── dashboard.css      # Estilos específicos de dashboard
│   └── components/        # Componentes UI reutilizables
│       ├── modal.css
│       ├── table.css
│       └── notification.css
├── app.js                 # Entry point Stimulus
└── bootstrap.js           # Bootstrap Stimulus

templates/
├── base.html.twig         # Layout principal
├── components/            # Componentes Twig reutilizables
│   ├── modal.html.twig
│   ├── table.html.twig
│   ├── pagination.html.twig
│   └── notification.html.twig
└── dashboard/
    └── index.html.twig

assets/package.json        # Dependencias frontend (Vite, Stimulus, etc.)
assets/vite.config.js      # Configuración de Vite
```

## Opción 1: Twig + Alpine.js (Recomendada para dashboards)

### Por qué Alpine.js

- **Sin build step** — Funciona directamente con Twig
- **Reactividad declarativa** — `x-data`, `x-show`, `x-model`
- **Ligero** — ~15kb gzipped
- **Integración perfecta** — Se inserta directamente en HTML

### Setup

```bash
# En assets/package.json
{
  "dependencies": {
    "@symfony/stimulus-bundle": "*",
    "alpinejs": "^3.14",
    "htmx.org": "^2.0"
  }
}
```

### Patrón: Dashboard con Alpine.js

```html
{# templates/dashboard/index.html.twig #}
{% extends 'base.html.twig' %}

{% block stylesheets %}
    <link rel="stylesheet" href="{{ asset('styles/dashboard.css') }}">
{% endblock %}

{% block body %}
<div class="dashboard"
     x-data="dashboard()"
     x-init="initDashboard()"
     hx-boost="true">

    <!-- Sidebar -->
    <aside class="sidebar">
        <h1>📚 Book Manager</h1>
        <nav>
            <template x-for="item in navItems">
                <a :href="item.href"
                   :class="{ 'active': currentView === item.view }"
                   @click.prevent="switchView(item.view)">
                    <span x-text="item.label"></span>
                </a>
            </template>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <template x-for="stat in stats">
                <div class="stat-card">
                    <div class="value" x-text="stat.value"></div>
                    <div class="label" x-text="stat.label"></div>
                </div>
            </template>
        </div>

        <!-- Search & Actions -->
        <div class="toolbar">
            <input type="text"
                   x-model="searchQuery"
                   @input.debounce.300ms="searchBooks()"
                   placeholder="Search by title or author...">
            <button class="btn btn-primary" @click="openCreateModal()">
                + New Book
            </button>
        </div>

        <!-- Books Table -->
        <div class="table-container" x-show="books.length > 0">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Description</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="book in paginatedBooks" :key="book.id">
                        <tr>
                            <td x-text="book.title"></td>
                            <td x-text="book.author"></td>
                            <td x-text="book.description || '—'"></td>
                            <td x-text="formatDate(book.createdAt)"></td>
                            <td class="actions">
                                <button @click="openEditModal(book)">Edit</button>
                                <button @click="confirmDelete(book)">Delete</button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Loading State -->
        <div class="empty-state" x-show="loading" x-cloak>
            Loading...
        </div>

        <!-- Pagination -->
        <div class="pagination" x-show="totalPages > 1">
            <button @click="prevPage" :disabled="currentPage === 1">Prev</button>
            <template x-for="page in totalPages" :key="page">
                <button @click="goToPage(page)" :class="{ active: page === currentPage }" x-text="page"></button>
            </template>
            <button @click="nextPage" :disabled="currentPage === totalPages">Next</button>
        </div>
    </main>
</div>

<!-- Create/Edit Modal -->
{% include 'components/modal.html.twig' with {
    id: 'book-modal',
    title: modalTitle,
    content: 'components/book_form.html.twig'
} %}

<!-- Delete Confirmation Modal -->
{% include 'components/modal.html.twig' with {
    id: 'delete-modal',
    title: 'Confirm Delete',
    content: 'components/confirm_delete.html.twig'
} %}
{% endblock %}

{% block javascripts %}
    <script src="https://unpkg.com/alpinejs@3.14.1/dist/cdn.min.js" defer></script>
    <script>
        function dashboard() {
            return {
                // State
                books: [],
                stats: [],
                searchQuery: '',
                currentPage: 1,
                totalPages: 1,
                loading: false,
                sse: null,

                // Modal state
                showModal: false,
                modalTitle: '',
                editingBook: null,
                deleteTarget: null,

                // Navigation
                navItems: [
                    { view: 'dashboard', label: 'Dashboard', href: '#' },
                ],
                currentView: 'dashboard',

                // Pagination config
                perPage: 10,

                async initDashboard() {
                    await Promise.all([
                        this.loadStats(),
                        this.loadBooks()
                    ]);
                    this.initSSE();
                },

                async loadStats() {
                    const res = await fetch('/dashboard/api/stats');
                    const data = await res.json();
                    this.stats = [
                        { label: 'Total Books', value: data.totalBooks },
                        { label: 'Created Today', value: data.createdToday },
                        { label: 'Updated Today', value: data.updatedToday },
                        { label: 'With Description', value: data.booksWithDescription },
                        { label: 'Without Description', value: data.booksWithoutDescription },
                    ];
                },

                async loadBooks() {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams({
                            page: this.currentPage,
                            limit: this.perPage,
                            ...(this.searchQuery && { q: this.searchQuery })
                        });
                        const res = await fetch(`/dashboard/api/search?${params}`);
                        const data = await res.json();
                        this.books = data.books;
                        this.totalPages = data.totalPages;
                    } catch (err) {
                        console.error('Failed to load books:', err);
                    } finally {
                        this.loading = false;
                    }
                },

                searchBooks() {
                    this.currentPage = 1;
                    this.loadBooks();
                },

                get paginatedBooks() {
                    return this.books;
                },

                prevPage() {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                        this.loadBooks();
                    }
                },

                nextPage() {
                    if (this.currentPage < this.totalPages) {
                        this.currentPage++;
                        this.loadBooks();
                    }
                },

                goToPage(page) {
                    this.currentPage = page;
                    this.loadBooks();
                },

                // Modal methods
                openCreateModal() {
                    this.modalTitle = 'Create New Book';
                    this.editingBook = null;
                    this.showModal = true;
                },

                openEditModal(book) {
                    this.modalTitle = 'Edit Book';
                    this.editingBook = { ...book };
                    this.showModal = true;
                },

                closeModal() {
                    this.showModal = false;
                    this.editingBook = null;
                },

                confirmDelete(book) {
                    this.deleteTarget = book;
                    document.getElementById('delete-modal').classList.add('active');
                },

                async deleteBook() {
                    if (!this.deleteTarget) return;
                    try {
                        const res = await fetch(`/dashboard/api/books/${this.deleteTarget.id}`, {
                            method: 'DELETE'
                        });
                        if (res.ok) {
                            document.getElementById('delete-modal').classList.remove('active');
                            await this.loadBooks();
                            await this.loadStats();
                        }
                    } catch (err) {
                        console.error('Delete failed:', err);
                    }
                    this.deleteTarget = null;
                },

                async saveBook(formData) {
                    const url = this.editingBook
                        ? `/dashboard/api/books/${this.editingBook.id}`
                        : '/dashboard/api/books';
                    const method = this.editingBook ? 'PUT' : 'POST';

                    try {
                        const res = await fetch(url, {
                            method,
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(formData)
                        });
                        const data = await res.json();
                        if (res.ok) {
                            this.closeModal();
                            await this.loadBooks();
                            await this.loadStats();
                        }
                    } catch (err) {
                        console.error('Save failed:', err);
                    }
                },

                // SSE for real-time updates
                initSSE() {
                    this.sse = new EventSource('/dashboard/sse');
                    this.sse.addEventListener('book.created', (e) => {
                        this.loadBooks();
                        this.loadStats();
                    });
                    this.sse.addEventListener('book.updated', (e) => {
                        this.loadBooks();
                        this.loadStats();
                    });
                    this.sse.addEventListener('book.deleted', (e) => {
                        this.loadBooks();
                        this.loadStats();
                    });
                },

                formatDate(dateStr) {
                    return new Date(dateStr).toLocaleDateString();
                },

                switchView(view) {
                    this.currentView = view;
                },
            };
        }
    </script>
{% endblock %}
```

## Patrón: Componente Modal Reutilizable

```html
{# templates/components/modal.html.twig #}
<div class="modal-overlay {{ id == delete_modal ? '' : 'active' }}"
     id="{{ id }}"
     x-show="{{ showModal|default('false') }}"
     @click.self="$el.classList.remove('active')">
    <div class="modal" role="dialog" aria-modal="true">
        <div class="modal-header">
            <h3>{{ title }}</h3>
            <button class="close-btn" @click="{{ closeAction|default('closeModal') }}">
                &times;
            </button>
        </div>
        <div class="modal-body">
            {% include content %}
        </div>
        {% if actions is defined %}
        <div class="modal-footer">
            {% for action in actions %}
                <button {{ action.class|default('') }} {{ action.event|default('') }}>
                    {{ action.label }}
                </button>
            {% endfor %}
        </div>
        {% endif %}
    </div>
</div>
```

```html
{# templates/components/book_form.html.twig #}
<form id="book-form" @submit.prevent="saveBook({{ formMapping|default('title: $refs.title.value, author: $refs.author.value') }})">
    <div class="form-group">
        <label for="book-title">Title *</label>
        <input type="text"
               id="book-title"
               x-ref="title"
               x-model="editingBook ? editingBook.title : ''"
               required>
    </div>
    <div class="form-group">
        <label for="book-author">Author *</label>
        <input type="text"
               id="book-author"
               x-ref="author"
               x-model="editingBook ? editingBook.author : ''"
               required>
    </div>
    <div class="form-group">
        <label for="book-description">Description</label>
        <textarea id="book-description"
                  x-ref="description"
                  x-model="editingBook ? editingBook.description : ''"></textarea>
    </div>
</form>
```

## Opción 2: HTMX (Server-Driven UI)

### Por qué HTMX

- **Cero JavaScript** — Todo se declara en HTML
- **Server-driven** — El backend controla la UI
- **Ideal para CRUDs** — Reemplaza boilerplate AJAX
- **Complementa Alpine.js** — HTMX para requests, Alpine para estado

### Patrón: CRUD con HTMX + Alpine.js

```html
{# templates/components/confirm_delete.html.twig #}
<p>Are you sure you want to delete "<strong>{{ deleteTarget.title ?? '' }}</strong>"?</p>
<div class="modal-actions">
    <button class="btn" @click="document.getElementById('delete-modal').classList.remove('active')">
        Cancel
    </button>
    <button class="btn btn-danger"
            hx-delete="/dashboard/api/books/{{ deleteTarget.id ?? '' }}"
            hx-target="#delete-modal"
            hx-swap="innerHTML"
            @click="deleteBook()">
        Delete
    </button>
</div>
```

### Patrón: Tabla con HTMX y Alpine.js

```html
{# HTMX maneja las peticiones, Alpine maneja el estado local #}
<div x-data="{ loading: false }">
    <div class="search-bar">
        <input type="text"
               name="q"
               x-model="searchQuery"
               @input.debounce.300ms="
                   loading = true;
                   htmx.ajax('GET', '/dashboard/api/search?q=' + encodeURIComponent($el.value), { target: '#books-table', swap: 'innerHTML' })
                       .finally(() => loading = false)
               ">
        <button class="btn btn-primary" @click="openCreateModal()">+ New Book</button>
    </div>

    <div id="books-table" hx-get="/dashboard/api/search?page=1&limit=10" hx-trigger="load">
        <!-- HTMX injects content here -->
    </div>
</div>
```

## Opción 3: Symfony UX (Ecosistema Oficial)

### Symfony UX Stimulus

Stimulus es el controlador JS oficial de Symfony. Ideal para componentes reutilizables.

```javascript
// assets/controllers/hello_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { url: String };

    connect() {
        this.fetchData();
    }

    async fetchData() {
        const response = await fetch(this.urlValue);
        const data = await response.json();
        this.element.innerHTML = this.render(data);
    }

    render(data) {
        return data.map(book => `
            <tr>
                <td>${book.title}</td>
                <td>${book.author}</td>
            </tr>
        `).join('');
    }
}
```

```html
{# Twig con Stimulus #}
<div data-controller="hello"
     data-hello-url-value="/dashboard/api/search"
     data-hello-loading-value="Loading...">
    {{ 'Loading...' }}
</div>
```

### Symfony UX Turbo

Turbo (antes TurboLinks) para navegación más rápida sin recarga completa.

```yaml
# config/packages/ux_turbo.yaml
framework:
    turbo:
        # Configurations
        fifo_fifo: false
```

## CSS Architecture

### Estructura de Estilos

```css
/* assets/styles/app.css - Variables globales */
:root {
    --color-primary: #1a1a2e;
    --color-secondary: #16213e;
    --color-accent: #0f3460;
    --color-success: #28a745;
    --color-danger: #dc3545;
    --color-warning: #ffc107;
    --color-info: #17a2b8;
    --color-text: #333;
    --color-text-light: #666;
    --color-bg: #f5f5f5;
    --color-white: #fff;
    --color-border: #ddd;
    --radius-sm: 5px;
    --radius-md: 8px;
    --radius-lg: 10px;
    --shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
    --shadow-md: 0 4px 8px rgba(0,0,0,0.15);
    --transition: all 0.2s ease;
}

/* Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: var(--color-bg);
    color: var(--color-text);
    line-height: 1.6;
}

/* Layout */
.dashboard {
    display: grid;
    grid-template-columns: 250px 1fr;
    min-height: 100vh;
}

/* Sidebar */
.sidebar {
    background: var(--color-primary);
    color: var(--color-white);
    padding: 20px;
}

.sidebar h1 {
    font-size: 1.5rem;
    margin-bottom: 30px;
}

.sidebar nav a {
    display: block;
    padding: 10px 15px;
    color: #aaa;
    text-decoration: none;
    border-radius: var(--radius-sm);
    margin-bottom: 5px;
    transition: var(--transition);
}

.sidebar nav a:hover,
.sidebar nav a.active {
    background: var(--color-secondary);
    color: var(--color-white);
}

/* Main Content */
.main {
    padding: 20px;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.stat-card {
    background: var(--color-white);
    padding: 20px;
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
}

.stat-card .value {
    font-size: 2rem;
    font-weight: bold;
    color: var(--color-primary);
}

.stat-card .label {
    color: var(--color-text-light);
    font-size: 0.9rem;
    margin-top: 5px;
}

/* Buttons */
.btn {
    padding: 10px 20px;
    border: none;
    border-radius: var(--radius-sm);
    cursor: pointer;
    font-size: 0.9rem;
    transition: var(--transition);
}

.btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

.btn-primary { background: var(--color-primary); color: var(--color-white); }
.btn-success { background: var(--color-success); color: var(--color-white); }
.btn-danger  { background: var(--color-danger);  color: var(--color-white); }
.btn-warning { background: var(--color-warning); color: #000; }
.btn-sm { padding: 5px 10px; font-size: 0.8rem; }

/* Table */
.table-container {
    background: var(--color-white);
    border-radius: var(--radius-md);
    overflow: hidden;
    box-shadow: var(--shadow-sm);
}

.table-container table {
    width: 100%;
    border-collapse: collapse;
}

.table-container th,
.table-container td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid var(--color-border);
}

.table-container th {
    background: #f8f9fa;
    font-weight: 600;
}

.table-container tr:hover {
    background: #f8f9fa;
}

.actions {
    display: flex;
    gap: 5px;
}

/* Modal */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal-overlay.active {
    display: flex;
}

.modal {
    background: var(--color-white);
    padding: 30px;
    border-radius: var(--radius-lg);
    width: 90%;
    max-width: 500px;
    box-shadow: var(--shadow-md);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.modal-header h3 {
    font-size: 1.3rem;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--color-text-light);
}

.modal-footer {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}

/* Form */
.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 10px;
    border: 1px solid var(--color-border);
    border-radius: var(--radius-sm);
    font-size: 1rem;
    transition: var(--transition);
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(26,26,46,0.1);
}

.form-group textarea {
    min-height: 100px;
    resize: vertical;
}

/* Notification */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    border-radius: var(--radius-sm);
    color: var(--color-white);
    z-index: 2000;
    animation: slideIn 0.3s ease;
    box-shadow: var(--shadow-md);
}

.notification.success { background: var(--color-success); }
.notification.error   { background: var(--color-danger); }
.notification.info    { background: var(--color-info); }

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

/* Pagination */
.pagination {
    display: flex;
    gap: 5px;
    justify-content: center;
    margin-top: 15px;
}

.pagination button {
    padding: 8px 12px;
    border: 1px solid var(--color-border);
    background: var(--color-white);
    border-radius: var(--radius-sm);
    cursor: pointer;
    transition: var(--transition);
}

.pagination button.active {
    background: var(--color-primary);
    color: var(--color-white);
    border-color: var(--color-primary);
}

.pagination button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px;
    color: var(--color-text-light);
}

/* Loading */
.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid var(--color-border);
    border-top-color: var(--color-primary);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard {
        grid-template-columns: 1fr;
    }

    .sidebar {
        padding: 15px;
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .modal {
        width: 95%;
        padding: 20px;
    }
}

/* Utility */
.x-cloak { display: none !important; }
.hidden { display: none; }
.sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); border: 0; }
```

## Patrón: SSE (Server-Sent Events) para Real-time

```javascript
// SSE Manager — Reutilizable en cualquier componente
class SSEManager {
    constructor(url, eventHandlers) {
        this.url = url;
        this.handlers = eventHandlers;
        this.eventSource = null;
        this.reconnectDelay = 1000;
        this.maxReconnectDelay = 30000;
    }

    connect() {
        this.eventSource = new EventSource(this.url);

        this.eventSource.onopen = () => {
            console.log('SSE connected');
            this.reconnectDelay = 1000;
        };

        for (const [event, handler] of Object.entries(this.handlers)) {
            this.eventSource.addEventListener(event, (e) => {
                try {
                    const data = JSON.parse(e.data);
                    handler(data);
                } catch (err) {
                    console.error(`SSE ${event} parse error:`, err);
                }
            });
        }

        this.eventSource.onerror = () => {
            console.warn('SSE connection lost, reconnecting...');
            this.eventSource.close();
            setTimeout(() => this.connect(), this.reconnectDelay);
            this.reconnectDelay = Math.min(this.reconnectDelay * 2, this.maxReconnectDelay);
        };
    }

    disconnect() {
        if (this.eventSource) {
            this.eventSource.close();
        }
    }
}

// Uso con Alpine.js
function dashboard() {
    return {
        sse: null,

        initSSE() {
            this.sse = new SSEManager('/dashboard/sse', {
                'book.created': (data) => {
                    this.loadBooks();
                    this.loadStats();
                    this.showNotification(`New book: ${data.title}`, 'success');
                },
                'book.updated': (data) => {
                    this.loadBooks();
                    this.loadStats();
                    this.showNotification(`Book updated: ${data.title}`, 'info');
                },
                'book.deleted': (data) => {
                    this.loadBooks();
                    this.loadStats();
                    this.showNotification(`Book deleted: ${data.title}`, 'error');
                },
            });
            this.sse.connect();
        },
    };
}
```

## Patrón: Notificaciones Reutilizable

```html
{# templates/components/notification.html.twig #}
<div class="notification {{ type }}" x-data="{ show: false }"
     x-show="show" x-transition.duration.300ms
     @notify.window="show = true; setTimeout(() => show = false, 3000)">
    <span x-text="event.detail.message"></span>
</div>

<script>
    // Helper global de notificaciones
    window.showNotification = function(message, type = 'info') {
        window.dispatchEvent(new CustomEvent('notify', {
            detail: { message, type }
        }));
    };
</script>
```

## Patrón: Tabla Interactiva con HTMX

```html
{# templates/components/book_table.html.twig #}
<table>
    <thead>
        <tr>
            <th>Title</th>
            <th>Author</th>
            <th>Description</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody id="books-tbody"
           hx-get="/dashboard/api/search?page={{ page }}&limit={{ per_page }}"
           hx-trigger="load"
           hx-swap="innerHTML">
        {% for book in books %}
        <tr>
            <td>{{ book.title }}</td>
            <td>{{ book.author }}</td>
            <td>{{ book.description ?: '—' }}</td>
            <td>{{ book.createdAt|date('Y-m-d') }}</td>
            <td class="actions">
                <button hx-get="/dashboard/api/books/{{ book.id }}"
                        hx-target="#book-modal"
                        hx-swap="innerHTML"
                        @click="window.openEditModal({{ book|json_encode|raw }})">
                    Edit
                </button>
                <button class="btn btn-danger btn-sm"
                        @click="window.confirmDelete({{ book.id }}, '{{ book.title|e('js') }}')">
                    Delete
                </button>
            </td>
        </tr>
        {% endfor %}
    </tbody>
</table>
```

## Patrón: Paginación con HTMX

```html
{# templates/components/pagination.html.twig #}
<div class="pagination">
    {% if page > 1 %}
        <button hx-get="/dashboard/api/search?page={{ page - 1 }}&limit={{ per_page }}&q={{ query }}"
                hx-target="#books-tbody"
                hx-swap="innerHTML"
                hx-indicator="#books-tbody">
            Prev
        </button>
    {% else %}
        <button disabled>Prev</button>
    {% endif %}

    {% for i in 1..totalPages %}
        <button hx-get="/dashboard/api/search?page={{ i }}&limit={{ per_page }}&q={{ query }}"
                hx-target="#books-tbody"
                hx-swap="innerHTML"
                hx-indicator="#books-tbody"
                {% if i == page %}class="active"{% endif %}>
            {{ i }}
        </button>
    {% endfor %}

    {% if page < totalPages %}
        <button hx-get="/dashboard/api/search?page={{ page + 1 }}&limit={{ per_page }}&q={{ query }}"
                hx-target="#books-tbody"
                hx-swap="innerHTML"
                hx-indicator="#books-tbody">
            Next
        </button>
    {% else %}
        <button disabled>Next</button>
    {% endif %}
</div>
```

## Patrón: Fetch API con Error Handling

```javascript
// API Client reutilizable — centraliza fetch calls
const API = {
    async get(url, options = {}) {
        const res = await fetch(url, options);
        if (!res.ok) {
            const error = await res.json().catch(() => ({ error: 'Request failed' }));
            throw new Error(error.error || `HTTP ${res.status}`);
        }
        return res.json();
    },

    async post(url, data) {
        return this._request('POST', url, data);
    },

    async put(url, data) {
        return this._request('PUT', url, data);
    },

    async delete(url) {
        return this._request('DELETE', url);
    },

    async _request(method, url, data = null) {
        const options = {
            method,
            headers: { 'Content-Type': 'application/json' },
        };
        if (data) {
            options.body = JSON.stringify(data);
        }
        const res = await fetch(url, options);
        if (!res.ok) {
            const error = await res.json().catch(() => ({ error: 'Request failed' }));
            throw new Error(error.error || error.message || `HTTP ${res.status}`);
        }
        if (res.status === 204) return null;
        return res.json();
    }
};

// Uso:
// const books = await API.get('/dashboard/api/search?page=1&limit=10');
// const book = await API.post('/dashboard/api/books', { title, author, description });
// await API.put(`/dashboard/api/books/${id}`, { title, author });
// await API.delete(`/dashboard/api/books/${id}`);
```

## Patrón: Escape HTML (XSS Prevention)

```javascript
// Siempre escapar datos del usuario en el frontend
function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    const div = document.createElement('div');
    div.textContent = String(str);
    return div.innerHTML;
}

// En Alpine.js, usar x-text (auto-escapes) en lugar de {