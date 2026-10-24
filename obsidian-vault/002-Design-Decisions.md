---
type: adr
status: accepted
date: 2026-10-22
---

Ver [[001-Architecture]] para vista general de arquitectura.

---

# ADR-001: Arquitectura Hexagonal

## Contexto
Proyecto Symfony con necesidad de separar lógica de negocio de infraestructura.

## Decisión
Adoptar arquitectura hexagonal con puertos (interfaces) y adaptadores (implementaciones).

## Consecuencias
- **+** Tests unitarios sin mockear infraestructura
- **+** Fácil cambiar DB o framework
- **+** Código más mantenible
- **-** Más archivos/interfaces iniciales
- **-** Curva de aprendizaje

---

# ADR-002: UUID como Primary Key

## Contexto
Necesitamos IDs únicos sin secuencias de base de datos.

## Decisión
Usar UUID de 128 bits generado en PHP con `bin2hex(random_bytes(16))`.

## Consecuencias
- **+** IDs únicos sin auto-increment
- **+** No expone cantidad de registros
- **-** Índices más grandes que INT
- **-** No ordenable temporalmente

---

# ADR-003: Title Hash MD5 para Búsquedas

## Contexto
Búsqueda eficiente por título sin full-text search.

## Decisión
Calcular MD5 del título normalizado (minúsculas + trim) como índice.

## Consecuencias
- **+** Búsqueda O(1) con índice
- **+** Simple de implementar
- **-** MD5 no es criptográfico (no importa aquí)
- **-** Colisiones posibles (raro con MD5)

---

# ADR-004: Rate Limiting en Memoria

## Contexto
Proteger API de abuso sin dependencia externa.

## Decisión
Array en memoria con filtrado por timestamp, 100 req/min/IP.

## Consecuencias
- **+** Sin dependencias adicionales
- **+** Rápido (sin DB queries)
- **-** No funciona con múltiples workers
- **-** Se pierde al reiniciar

---

# ADR-005: DTOs con Validación

## Contexto
Validar datos de entrada antes de procesar.

## Decisión
DTOs readonly con método `validate()` propio.

## Consecuencias
- **+** Validación centralizada
- **+** Type safety con PHP 8.3 readonly
- **+** Mensajes de error claros
- **-** Boilerplate por cada DTO

---

# ADR-006: Value Object Title

## Contexto
El título del libro tiene invariantes de negocio que deben protegerse (no vacío, máximo 255 caracteres).

## Decisión
Crear `Title` como Value Object en `src/Domain/ValueObject/Title.php` con validación en constructor.

## Consecuencias
- **+** Invariantes de dominio protegidas
- **+** Código más expresivo
- **+** Reutilizable entre Domain y Application
- **-** Requiere mapeo entre VO y entidad Doctrine
- **-** Boilerplate adicional

---

# ADR-007: Domain Events

## Contexto
Necesitamos reaccionar a cambios en el dominio (creación/actualización de libros) sin acoplar lógica de negocio a infraestructura.

## Decisión
Implementar Domain Events (`BookCreatedEvent`, `BookUpdatedEvent`) con `DomainEventListener` en capa de infraestructura.

## Consecuencias
- **+** Dominio independiente de infraestructura
- **+** Fácil añadir nuevos handlers
- **+** Cumple DDD (Domain-Driven Design)
- **-** Mayor complejidad inicial
- **-** Debugging más complejo

---

# ADR-008: Dashboard API Separada

## Contexto
El frontend del dashboard necesita endpoints específicos (CRUD, búsqueda, estadísticas, SSE) diferentes de la API REST pública.

## Decisión
Crear `DashboardController` con endpoints en `/dashboard/api/` separados de la API REST (`/api/books`). Incluir SSE para actualizaciones en tiempo real.

## Consecuencias
- **+** Separación clara de responsabilidades
- **+** Diferentes requisitos de seguridad/performance
- **+** SSE nativo para actualizaciones en tiempo real
- **-** Código duplicado entre controllers (mitigado con servicios compartidos)

---

# ADR-009: Testing Multi-Capa (PHPUnit + Behat + Playwright)

## Contexto
Necesitamos cubrir diferentes niveles de testing: unit tests, API contracts, y UI interactiva.

## Decisión
Usar tres herramientas complementarias:
- **PHPUnit** para unit tests de services, DTOs, value objects
- **Behat** para BDD tests de contratos API (REST + Dashboard API)
- **Playwright** para E2E tests del Dashboard con Alpine.js, modales, SSE

## Consecuencias
- **+** Cobertura completa: código → API → UI
- **+** Behat usa Gherkin (legible por no-devs)
- **+** Playwright con data-testid para selecciones estables
- **+** `make test-all` ejecuta todo junto
- **-** Tres configuraciones diferentes para mantener
- **-** Playwright requiere Node.js 20+
- **-** Behat requiere kernel boot en tests (más lento que unit)
