---
type: overview
project: Book API
stack: "PHP 8.3.36, Symfony 7.1, MariaDB 10.11, Nginx, Docker"
date: 2026-10-22
---

# Book API — Project Overview

## Objetivo
API REST para gestión de libros con arquitectura hexagonal.

## Stack Tecnológico
- **PHP** 8.3.36 (parche de seguridad emergency)
- **Symfony** 7.1 (Framework)
- **MariaDB** 10.11 (Base de datos)
- **Nginx** (Proxy inverso)
- **Docker Compose** (Orquestación)

## Arquitectura
Véase [[001-Architecture]] para detalles.

Arquitectura Hexagonal (Ports & Adapters):
```
Dominio (Entity, ValueObject, DomainEvent)
    ↑
Aplicación (Service, DTO, Port Interface)
    ↑
Adaptadores (Controller, Repository, Middleware)
```

## Endpoints Actuales

### REST API (`/api/books`)
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/books` | Listar libros (cache 60s) |
| GET | `/api/books/{id}` | Obtener libro |
| POST | `/api/books` | Crear libro |
| PUT | `/api/books/{id}` | Actualizar libro |

### Dashboard API (`/dashboard/api/`)
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/dashboard/api/stats` | Estadísticas generales |
| GET | `/dashboard/api/search` | Búsqueda de libros |
| POST | `/dashboard/api/books` | Crear libro |
| GET | `/dashboard/api/books/{id}` | Obtener libro |
| PUT | `/dashboard/api/books/{id}` | Actualizar libro |
| DELETE | `/dashboard/api/books/{id}` | Eliminar libro |

### SSE
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/dashboard/sse` | Server-Sent Events para actualizaciones en tiempo real |

## Features Implementadas
- Rate limiting (100 req/min/IP)
- Security headers (5 headers)
- HTTP Cache en listados
- DTO Groups para serialización
- UUID como primary key
- Title hash MD5 para búsquedas
- Title Value Object con validación
- Domain Events (BookCreatedEvent, BookUpdatedEvent)
- Domain Event Listener
- Dashboard con CRUD completo
- SSE para actualizaciones en tiempo real
- Statistics service
- Search service con pagination
- BookChangeTrackerService
- Tests unitarios (DTO, Service, Value Object)
- Tests E2E (Dashboard)
- Tests Integration (Dashboard API)
- PHPStan Level 7
- PHP-CS-Fixer (PSR-12 + Symfony)
- Pre-commit hook

## Estado
- [x] Infraestructura Docker funcionando
- [x] API CRUD básica
- [x] Rate limiting
- [x] Security headers
- [x] HTTP Cache
- [x] DTOs con validación
- [x] Value Object `Title`
- [x] Domain Events (BookCreatedEvent, BookUpdatedEvent)
- [x] Dashboard con CRUD completo
- [x] SSE para actualizaciones en tiempo real
- [x] Statistics y Search services
- [x] Tests unitarios (DTO, Service, Value Object)
- [x] Tests E2E e Integration
- [ ] Value Object `ISBN`
- [ ] Search strategy pattern (exact vs fuzzy)
- [ ] Pagination en listados API REST
- [ ] API documentation con OpenAPI/Swagger
