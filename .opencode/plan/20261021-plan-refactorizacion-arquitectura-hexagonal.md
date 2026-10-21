# Plan de Refactorización: Arquitectura Hexagonal + Patrones de Diseño

**Fecha**: 2026-10-21

---

## 1. Nueva Estructura de Directorios

```
src/
├── Controller/                    # Adaptadores HTTP (entrada)
│   └── BookController.php         # Delgado: solo orquesta
├── Domain/                        # Núcleo del dominio (framework-free)
│   ├── Entity/
│   │   └── Book.php               # Entidad de dominio pura
│   ├── ValueObject/
│   │   └── Title.php              # Inmutable, con hash computation
│   └── DomainEvent/
│       ├── BookCreatedEvent.php
│       └── BookUpdatedEvent.php
├── Application/                   # Capa de aplicación (puertos + orquestación)
│   ├── Service/
│   │   └── BookApplicationService.php
│   ├── BookApplicationServiceInterface.php  # Puerto de entrada
│   ├── DTO/
│   │   ├── CreateBookDTO.php
│   │   ├── UpdateBookDTO.php
│   │   └── BookResponseDTO.php
│   └── Port/
│       └── BookRepositoryInterface.php      # Puerto de salida
├── Infrastructure/                # Adaptadores concretos
│   ├── Entity/
│   │   └── Book.php               # Mapeo Doctrine (hereda/usa dominio)
│   ├── Repository/
│   │   └── BookRepository.php     # Implementación Doctrine del puerto
│   ├── Middleware/
│   │   ├── SecurityHeadersListener.php
│   │   └── RateLimitingMiddleware.php
│   └── EventListener/
│       └── DomainEventListener.php
```

---

## 2. Capa de Dominio (Framework-Independent)

**ValueObject `Title`**: Inmutable, valida longitud, computa hash MD5. Reemplaza la lógica de `titleHash` dispersa en la entidad.

**Entidad `Book`**: Pura, sin Doctrine, sin Symfony. Genera ID con `bin2hex(random_bytes(16))`. Encapsula reglas de dominio.

**Domain Events**: `BookCreatedEvent` y `BookUpdatedEvent` para desacoplar efectos secundarios (logs, notificaciones, cache).

---

## 3. Capa de Aplicación (Ports)

**Puerto de Entrada** (`BookApplicationServiceInterface`): Define los casos de uso — `listBooks()`, `getBook()`, `createBook()`, `updateBook()`, `deleteBook()`.

**Puerto de Salida** (`BookRepositoryInterface`): Define la interfaz de persistencia — `save()`, `delete()`, `find()`, `findAll()`, `findByTitleHash()`.

**DTOs**: `CreateBookDTO` y `UpdateBookDTO` con validación explícita y método `fromArray()`. Separan datos de entrada de entidades.

**Servicio** (`BookApplicationService`): Orquesta el flujo — valida DTO, checkea unicidad, persiste, dispara eventos.

---

## 4. Capa de Infraestructura (Adapters)

**Doctrine Entity**: Mapeo ORM separado del dominio. El repository convierte entre `Domain\Book` ↔ `Infrastructure\Entity\Book`.

**Repository**: Implementa `BookRepositoryInterface`, usa Doctrine internamente. Hace `toDomain()` / `toDoctrine()` para traducir entre capas.

**Middleware**: `SecurityHeadersListener` (event listener) y `RateLimitingMiddleware` extraídos del controller. Comportamiento transversal desacoplado.

**Controller Refactorizado**: Delgado (~80 líneas → ~50 líneas). Solo decodifica JSON → crea DTO → llama service → devuelve JSON. Sin EntityManager, sin validación manual, sin rate limiting inline.

---

## 5. Patrones de Diseño Aplicados

| Patrón | Ubicación | Propósito |
|--------|-----------|-----------|
| **Factory** | `DTO::fromArray()` | Crear DTOs inmutables desde arrays/JSON |
| **Strategy** | `BookRepositoryInterface` + `InMemoryBookRepository` | Cambiar implementación de persistencia |
| **Decorator** | `RateLimitingMiddleware`, `SecurityHeadersListener` | Comportamiento transversal inyectado |
| **Observer** | `EventDispatcher` + Domain Events | Desacoplar efectos secundarios |
| **Adapter** | Repository (Doctrine→Domain), Controller (HTTP→Application) | Traducir entre capas y protocolos |
| **Value Object** | `Title` | Encapsular regla de dominio + hash |
| **DTO** | `CreateBookDTO`, `UpdateBookDTO` | Separar datos de entrada/salida |

---

## 6. Estrategia de Testing Unitario

**Repositorio InMemory**: Implementa `BookRepositoryInterface` con array en memoria. Permite tests rápidos sin base de datos.

```php
final class InMemoryBookRepository implements BookRepositoryInterface
{
    private array $books = [];
    public function save(Book $book): void { $this->books[$book->getId()] = $book; }
    public function delete(Book $book): void { unset($this->books[$book->getId()]); }
    public function find(string $id): ?Book { return $this->books[$id] ?? null; }
    public function findAll(): array { return array_values($this->books); }
    public function findByTitleHash(string $titleHash): array { /* filter */ }
}
```

**Tests a crear**:
- `tests/Domain/ValueObject/TitleTest.php` — validación, hash, inmutabilidad
- `tests/Application/DTO/CreateBookDTOTest.php` — validación de campos
- `tests/Application/Service/BookApplicationServiceTest.php` — CRUD con InMemory repo, eventos, excepciones
- `tests/Infrastructure/Middleware/RateLimitingMiddlewareTest.php` — límites, window
- `tests/Infrastructure/Middleware/SecurityHeadersListenerTest.php` — headers presentes

**Tests funcionales existentes**: Se mantienen 12 tests adaptados al controller refactorizado (no se testean directamente, pero se verifica que la API responda igual).

---

## 7. Fases de Migración

| Fase | Archivos | Tiempo |
|------|----------|--------|
| **1. Dominio** | `Domain/ValueObject/Title.php`, `Domain/Entity/Book.php`, Domain Events | Días 1-2 |
| **2. Aplicación** | `Port/`, `DTO/`, `BookApplicationServiceInterface`, `BookApplicationService.php` | Días 3-4 |
| **3. Infraestructura** | `Infrastructure/Entity/`, `Infrastructure/Repository/`, Middleware | Días 5-6 |
| **4. Controller** | Refactorizar `BookController.php` a adaptador delgado | Día 7 |
| **5. Testing** | InMemory repo, unit tests para service/DTOs/VOs/middleware | Días 8-9 |
| **6. Limpieza** | Eliminar `Entity/Book.php` antiguo, verificar API, actualizar Obsidian | Día 10 |

---

## 8. Archivos a Crear/Modificar

**Crear (19 nuevos)**:
- `src/Domain/ValueObject/Title.php`
- `src/Domain/Entity/Book.php`
- `src/Domain/DomainEvent/BookCreatedEvent.php`
- `src/Domain/DomainEvent/BookUpdatedEvent.php`
- `src/Application/Port/BookRepositoryInterface.php`
- `src/Application/BookApplicationServiceInterface.php`
- `src/Application/BookApplicationService.php`
- `src/Application/DTO/CreateBookDTO.php`
- `src/Application/DTO/UpdateBookDTO.php`
- `src/Infrastructure/Entity/Book.php`
- `src/Infrastructure/Repository/BookRepository.php`
- `src/Infrastructure/Middleware/SecurityHeadersListener.php`
- `src/Infrastructure/Middleware/RateLimitingMiddleware.php`
- `src/Infrastructure/EventListener/DomainEventListener.php`
- `tests/Domain/ValueObject/TitleTest.php`
- `tests/Application/DTO/CreateBookDTOTest.php`
- `tests/Application/Service/BookApplicationServiceTest.php`
- `tests/Infrastructure/Middleware/RateLimitingMiddlewareTest.php`
- `tests/Infrastructure/Middleware/SecurityHeadersListenerTest.php`

**Modificar (4 existentes)**:
- `src/Controller/BookController.php` → adaptador delgado
- `config/services.yaml` → inyección de dependencias, middleware
- `phpunit.xml.dist` → nuevos directorios de tests
- `obsidian-vault/` → documentación de decisiones

**Eliminar (1)**:
- `src/Entity/Book.php` → reemplazado por `Infrastructure/Entity/Book.php`

---

## 9. Garantías Arquitecturales

| Principio | Verificación |
|-----------|--------------|
| **DIP** | Service depende de `BookRepositoryInterface`, no de Doctrine |
| **SRP** | Controller orquesta, Service valida/persiste, Repository maneja DB |
| **OCP** | Nuevas estrategias de búsqueda/serialización sin modificar service |
| **ISP** | `BookRepositoryInterface` pequeña y enfocada |
| **Framework Independence** | `Domain/` tiene cero imports de Symfony/Doctrine |
| **Testability** | `InMemoryBookRepository` permite tests rápidos sin DB |
| **Backward Compatibility** | Endpoints, estructura JSON y códigos HTTP idénticos |

---

## 10. Decisiones Pendientes

1. **¿Separar Doctrine Entity del Domain Entity** (2 clases) o **usar una sola con mapeo por atributos**? La separación es más limpia pero duplica código. Con una sola clase, el entity tiene tanto annotations Doctrine como lógica de dominio.

2. **¿Usar `symfony/validator` para DTOs** o mantener validación manual en `DTO::validate()`? Validator es más estándar pero añade dependencia. Validación manual es más simple y framework-agnostic.

3. **¿Mantener rate limiting in-memory** o migrar a Redis/external store? El actual es ephemeral por request. Para producción real necesitaría Redis, pero para tests unitarios el in-memory está bien.

4. **¿Ramsey UUID** para generación de IDs o mantener `bin2hex(random_bytes(16))`? Ramsey es más estándar pero añade dependencia. El proyecto ya tiene `ramsey/uuid-doctrine`.

---

## 11. Estado Actual del Proyecto

| Componente | Estado |
|------------|--------|
| **Skills configuradas** | ✅ `.opencode/skills/*` cargadas |
| **Agent default** | ✅ `hexagonal-architect` |
| **Docker services** | ✅ 3/3 levantados |
| **API endpoints** | ✅ Funcionando |
| **Database** | ✅ Migrations aplicadas |
| **Composer deps** | ✅ Instaladas |
| **Tests** | ⚠️ 6 passing, 4 failures, 2 errors (problemas previos) |

---

## 12. Próximos Pasos

1. Esperar aprobación del plan
2. Iniciar con Fase 1 (Dominio)
3. Ejecutar `php bin/phpunit` después de cada fase
4. Documentar decisiones en Obsidian vault
5. Verificar API contract unchanged con curl/Postman
