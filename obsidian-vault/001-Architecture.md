---
type: architecture
status: active
tags: [architecture, hexagonal, ports, adapters]
---

# Arquitectura Hexagonal — Book API

## Decisiones Arquitectónicas

### 1. Arquitectura Hexagonal (Ports & Adapters)
**Fecha:** 2026-10-20
**Justificación:** Separar dominio de infraestructura para facilitar testing y cambios tecnológicos.

### Estructura de Capas

```
┌──────────────────────────────────────────────────────────┐
│                   ADAPTADORES                            │
│  ┌──────────────┐  ┌──────────────┐  ┌────────────────┐  │
│  │ Controllers  │  │ Repositories │  │    Middleware  │  │
│  │ (HTTP Input) │  │   (DB Output)│  │ (HTTP Filter)  │  │
│  └──────────────┘  └──────────────┘  └────────────────┘  │
├──────────────────────────────────────────────────────────┤
│                   APLICACIÓN                             │
│  ┌────────────────────┐  ┌───────────────┐ ┌──────────┐ │
│  │ Service Interfaces │  │     DTOs      │ │   Ports  │ │
│  │ (Input Ports)      │  │ (Data Objects)│ │ Interf.  │ │
│  └────────────────────┘  └───────────────┘ └──────────┘ │
├──────────────────────────────────────────────────────────┤
│                       DOMINIO                            │
│  ┌──────────────┐  ┌──────────────┐  ┌───────────────┐  │
│  │   Entities   │  │ Value Objects│  │ Domain Events │  │
│  │ (Book)       │  │ (Title)      │  │ (Created,     │  │
│  └──────────────┘  └──────────────┘  │ Updated)      │  │
│                                      └───────────────┘  │
└──────────────────────────────────────────────────────────┘
          ↑                          ↑
     Dependencias                 Dependencias
     apuntan hacia                apuntan hacia
     adentro                      adentro
```

### Directorios

```
src/
├── Domain/                    # Capa de dominio (núcleo)
│   ├── Entity/                # Entidades de dominio
│   │   └── Book.php
│   ├── ValueObject/           # Value Objects
│   │   └── Title.php
│   └── DomainEvent/           # Domain Events
│       ├── BookCreatedEvent.php
│       └── BookUpdatedEvent.php
├── Application/               # Capa de aplicación
│   ├── Port/                  # Interfaces (puertos)
│   │   ├── BookRepositoryInterface.php
│   │   ├── BookSearchServiceInterface.php
│   │   └── BookChangeTrackerInterface.php
│   ├── Service/               # Servicios (interfaces)
│   │   ├── BookApplicationServiceInterface.php
│   │   └── BookStatisticsServiceInterface.php
│   ├── DTO/                   # Data Transfer Objects
│   │   ├── CreateBookDTO.php
│   │   ├── UpdateBookDTO.php
│   │   ├── SearchBooksDTO.php
│   │   └── BookStatisticsDTO.php
│   ├── BookApplicationService.php
│   ├── BookSearchService.php
│   ├── BookStatisticsService.php
│   └── BookChangeTrackerService.php
├── Infrastructure/            # Adaptadores de infraestructura
│   ├── Entity/                # Entidades ORM (Doctrine)
│   │   └── Book.php
│   ├── Repository/            # Repositorios (Doctrine)
│   │   └── BookRepository.php
│   ├── Middleware/            # Middlewares HTTP
│   │   ├── RateLimitingMiddleware.php
│   │   └── SecurityHeadersListener.php
│   └── EventListener/         # Event Listeners
│       └── DomainEventListener.php
├── Controller/                # Controladores HTTP
│   ├── BookController.php
│   └── DashboardController.php
└── Kernel.php
```

### Reglas de Dependencia
1. Dominio → Ninguna dependencia externa
2. Aplicación → Dominio (interfaces y entidades)
3. Adaptadores → Aplicación (implementa interfaces)

### Archivos Clave
- `src/Domain/Entity/Book.php` — Entidad de dominio — Ver [[010-Domain-Model]]
- `src/Domain/ValueObject/Title.php` — Value Object de título
- `src/Domain/DomainEvent/BookCreatedEvent.php` — Evento de dominio
- `src/Application/Port/BookRepositoryInterface.php` — Puerto de repositorio
- `src/Application/Service/BookApplicationServiceInterface.php` — Puerto de aplicación
- `src/Application/DTO/CreateBookDTO.php` — DTO de creación
- `src/Infrastructure/Entity/Book.php` — Entidad Doctrine ORM
- `src/Infrastructure/Repository/BookRepository.php` — Repositorio Doctrine
- `src/Controller/BookController.php` — Adaptador HTTP REST API
- `src/Controller/DashboardController.php` — Adaptador HTTP Dashboard API
- `src/Infrastructure/Middleware/RateLimitingMiddleware.php` — Rate limiting
- `src/Infrastructure/Middleware/SecurityHeadersListener.php` — Security headers
- `src/Infrastructure/EventListener/DomainEventListener.php` — Domain event listener

### Patrones Usados
Ver [[100-Patterns-Used]] para catálogo completo.

### Patrón de Inyección de Dependencias
```yaml
# config/services.yaml
services:
    # Application services
    App\BookApplicationServiceInterface:
        class: App\BookApplicationService
        arguments: ['@App\BookRepositoryInterface', '@App\BookSearchServiceInterface']
    
    # Ports (interfaces)
    App\BookRepositoryInterface:
        class: App\Infrastructure\Repository\BookRepository
    
    App\BookSearchServiceInterface:
        class: App\BookSearchService
        arguments: ['@App\BookRepositoryInterface']
```
