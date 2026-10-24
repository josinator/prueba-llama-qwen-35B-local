---
type: patterns
tags: [patterns, solid, design-patterns]
---

# Patrones de Diseño Aplicados

## SOLID

| Principio | Estado | Archivo |
|-----------|--------|---------|
| SRP | ✅ Implementado | BookController (solo orquesta), BookApplicationService |
| OCP | ✅ Implementado | Interfaces extensibles (BookRepositoryInterface, BookSearchServiceInterface) |
| LSP | ✅ Implementado | Repositorios sustituibles (InMemory en tests) |
| ISP | ✅ Implementado | Interfaces pequeñas y específicas |
| DIP | ✅ Implementado | Depende de interfaces, no concretos |

## Patrones GoF Usados

### Creacionales
- **Factory** — DTOs con métodos `fromArray()` / `fromRequest()`
- **Builder** — (Por implementar para entities complejas)

### Estructurales
- **Adapter** — Controllers y Repositories como adaptadores
- **Repository** — BookRepositoryInterface + BookRepository implementation
- **Strategy** — BookSearchService (título hash vs búsqueda exacta)
- **Decorator** — (Por implementar para logging/middleware)

### Conductuales
- **Observer** — Domain Events (BookCreatedEvent, BookUpdatedEvent) + DomainEventListener
- **Command** — (Por implementar para operaciones async)
- **Mediator** — BookApplicationService coordina servicios

## Patrones Symfony Usados

| Patrón | Uso | Archivo |
|--------|-----|---------|
| Service Locator | DI Container | config/services.yaml |
| Event Dispatcher | Domain Events | DomainEventListener |
| Data Transformer | Serialización | Serializer groups |
| Middleware | Rate limiting, Security headers | Middleware/ |
| Controller as Service | Controllers inyectan interfaces | Controller/ |

## Patrones Hexagonal Aplicados

| Patrón | Descripción | Ubicación |
|--------|-------------|-----------|
| Port | Interfaces en `Application/Port/` | BookRepositoryInterface, BookSearchServiceInterface |
| Adapter | Implementaciones en `Infrastructure/` | BookRepository, DomainEventListener |
| DTO | Transferencia de datos | Application/DTO/ |
| Domain Event | Eventos de dominio | Domain/DomainEvent/ |
| Value Object | Objetos inmutables | Domain/ValueObject/ |

## Próximos Patrones a Implementar
Ver [[200-TODOs]] para roadmap.
