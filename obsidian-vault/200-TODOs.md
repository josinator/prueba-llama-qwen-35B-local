---
type: todo
tags: [todo, roadmap]
---

# TODOs — Book API

## Prioridad Alta
- [x] Tests unitarios para BookApplicationService
- [x] Tests de integración para DashboardController
- [x] Value Object `Title`
- [x] Behat API BDD tests (CRUD, stats, search, error handling)
- [x] Playwright E2E tests (dashboard, modales, SSE, search)
- [ ] Value Object `ISBN`
- [ ] Pagination en listados API REST
- [ ] Search strategy pattern (exact vs fuzzy)

## Prioridad Media
- [x] Domain Event `BookCreatedEvent` ✅
- [x] Domain Event `BookUpdatedEvent` ✅
- [x] Domain Event Listener
- [x] Dashboard con CRUD completo
- [x] SSE para actualizaciones en tiempo real
- [ ] API documentation con OpenAPI/Swagger
- [ ] Soft delete para libros

## Prioridad Baja
- [ ] Categorías/Autores como entidades separadas
- [ ] Import/Export CSV
- [ ] Webhook para actualizaciones
- [ ] Metrics/Prometheus endpoints
