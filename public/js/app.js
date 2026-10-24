// Helper functions
function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Centralized API client
const API = {
    async get(url) {
        try {
            const response = await fetch(url);
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return await response.json();
        } catch (error) {
            console.error('API GET error:', error);
            throw error;
        }
    },

    async post(url, data) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return await response.json();
        } catch (error) {
            console.error('API POST error:', error);
            throw error;
        }
    },

    async put(url, data) {
        try {
            const response = await fetch(url, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return await response.json();
        } catch (error) {
            console.error('API PUT error:', error);
            throw error;
        }
    },

    async delete(url) {
        try {
            const response = await fetch(url, { method: 'DELETE' });
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return await response.json();
        } catch (error) {
            console.error('API DELETE error:', error);
            throw error;
        }
    }
};

// SSE Manager with exponential backoff
class SSEManager {
    constructor(url, eventSource) {
        this.url = url;
        this.eventSource = eventSource;
        this.reconnectDelay = 1000;
        this.maxReconnectDelay = 30000;
        this.reconnectAttempts = 0;
    }

    connect() {
        this.eventSource = new EventSource(this.url);

        this.eventSource.onopen = () => {
            this.reconnectDelay = 1000;
            this.reconnectAttempts = 0;
            this.onStatus('connected');
        };

        this.eventSource.onerror = (error) => {
            console.error('SSE connection error:', error);
            this.onStatus('disconnected');
            this.eventSource.close();

            // Exponential backoff
            setTimeout(() => {
                this.reconnectAttempts++;
                this.reconnectDelay = Math.min(this.reconnectDelay * 2, this.maxReconnectDelay);
                console.log(`Reconnecting in ${this.reconnectDelay}ms (attempt ${this.reconnectAttempts})`);
                this.connect();
            }, this.reconnectDelay);
        };

        this.eventSource.addEventListener('book.created', (e) => {
            try {
                const data = JSON.parse(e.data);
                if (this.onBookCreated) this.onBookCreated(data);
            } catch (err) {
                console.error('Failed to parse SSE book.created event:', err);
            }
        });

        this.eventSource.addEventListener('book.updated', (e) => {
            try {
                const data = JSON.parse(e.data);
                if (this.onBookUpdated) this.onBookUpdated(data);
            } catch (err) {
                console.error('Failed to parse SSE book.updated event:', err);
            }
        });

        this.eventSource.addEventListener('book.deleted', (e) => {
            try {
                const data = JSON.parse(e.data);
                if (this.onBookDeleted) this.onBookDeleted(data);
            } catch (err) {
                console.error('Failed to parse SSE book.deleted event:', err);
            }
        });
    }

    onStatus(status) {
        console.log('SSE Status:', status);
        if (this.onStatusChange) this.onStatusChange(status);
    }

    disconnect() {
        if (this.eventSource) {
            this.eventSource.close();
        }
    }
}

// Alpine.js Dashboard Component
Alpine.data('dashboard', () => ({
        books: [],
        stats: {
            totalBooks: 0,
            createdToday: 0,
            updatedToday: 0,
            booksWithDescription: 0,
            booksWithoutDescription: 0
        },
        currentPage: 1,
        totalPages: 1,
        searchQuery: '',
        sseStatus: 'disconnected',
        lastUpdated: 'Never',
        notifications: [],
        modal: {
            show: false,
            mode: 'create',
            bookId: '',
            title: '',
            author: '',
            description: ''
        },
        deleteModal: {
            show: false,
            bookId: '',
            bookTitle: ''
        },

        async init() {
            await this.loadBooks();
            await this.loadStats();
            this.setupSSE();
        },

        setupSSE() {
            const sseManager = new SSEManager('/dashboard/sse', null);
            sseManager.onBookCreated = (data) => this.handleBookCreated(data);
            sseManager.onBookUpdated = (data) => this.handleBookUpdated(data);
            sseManager.onBookDeleted = (data) => this.handleBookDeleted(data);
            sseManager.onStatusChange = (status) => {
                this.sseStatus = status;
            };
            sseManager.connect();
        },

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
        },

        async loadStats() {
            try {
                const data = await API.get('/dashboard/api/stats');
                Object.assign(this.stats, data);
            } catch (error) {
                console.error('Failed to load stats:', error);
            }
        },

        async searchBooks() {
            await this.loadBooks(1);
        },

        async handleBookCreated(data) {
            this.showNotification(`Book created: ${data.title}`, 'success');
            await this.loadBooks(this.currentPage);
            await this.loadStats();
        },

        async handleBookUpdated(data) {
            this.showNotification(`Book updated: ${data.title}`, 'info');
            await this.loadBooks(this.currentPage);
            await this.loadStats();
        },

        async handleBookDeleted(data) {
            this.showNotification(`Book deleted: ${data.title}`, 'error');
            await this.loadBooks(this.currentPage);
            await this.loadStats();
        },

        async openCreateModal() {
            this.modal = {
                show: true,
                mode: 'create',
                bookId: '',
                title: '',
                author: '',
                description: ''
            };
        },

        async openEditModal(bookId) {
            try {
                const book = await API.get(`/dashboard/api/books/${bookId}`);
                this.modal = {
                    show: true,
                    mode: 'edit',
                    bookId: book.id,
                    title: book.title,
                    author: book.author,
                    description: book.description || ''
                };
            } catch (error) {
                console.error('Failed to load book:', error);
                this.showNotification('Failed to load book', 'error');
            }
        },

        async handleSubmit() {
            try {
                const data = {
                    title: this.modal.title,
                    author: this.modal.author,
                    description: this.modal.description
                };

                if (this.modal.mode === 'create') {
                    await API.post('/dashboard/api/books', data);
                    this.showNotification('Book created successfully', 'success');
                } else {
                    await API.put(`/dashboard/api/books/${this.modal.bookId}`, data);
                    this.showNotification('Book updated successfully', 'success');
                }

                this.closeModal();
                await this.loadBooks(this.currentPage);
                await this.loadStats();
            } catch (error) {
                console.error('Failed to save book:', error);
                this.showNotification('Failed to save book', 'error');
            }
        },

        async openDeleteModal(bookId, bookTitle) {
            this.deleteModal = {
                show: true,
                bookId,
                bookTitle
            };
        },

        async deleteBook() {
            try {
                await API.delete(`/dashboard/api/books/${this.deleteModal.bookId}`);
                this.showNotification('Book deleted successfully', 'success');
                this.closeDeleteModal();
                await this.loadBooks(this.currentPage);
                await this.loadStats();
            } catch (error) {
                console.error('Failed to delete book:', error);
                this.showNotification('Failed to delete book', 'error');
            }
        },

        closeModal() {
            this.modal.show = false;
        },

        closeDeleteModal() {
            this.deleteModal.show = false;
        },

        showNotification(message, type = 'info') {
            const id = Date.now();
            this.notifications.push({ id, message, type });
            setTimeout(() => {
                this.notifications = this.notifications.filter(n => n.id !== id);
            }, 3000);
        }
    }));
