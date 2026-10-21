---
type: patterns
tags: [patterns, solid, design-patterns]
---

# Patrones de Diseño Aplicados

## SOLID

| Principio | Estado | Archivo |
|-----------|--------|---------|
| SRP | ✅ Implementado | BookController (solo orquesta) |
| OCP | ✅ Implementado | Interfaces extensibles |
| LSP | ✅ Implementado | Repositorios sustituibles |
| ISP | ✅ Implementado | Interfaces pequeñas |
| DIP | ✅ Implementado | Depende de interfaces |

## Patrones GoF Usados

### Creacionales
- **Factory** — BookDTO::fromArray()
- **Builder** — (Por implementar para entities complejas)

### Estructurales
- **Adapter** — Controllers y Repositories como adaptadores
- **Strategy** — (Por implementar para search strategies)
- **Decorator** — (Por implementar para logging/middleware)

### Conductuales
- **Observer** — EventDispatcher de Symfony
- **Command** — (Por implementar para operaciones async)
- **Repository** — BookRepository

## Próximos Patrones a Implementar
Ver [[200-TODOs]] para roadmap.

---

## Patrones Symfony Usados

| Patrón | Uso | Archivo |
|--------|-----|---------|
| Service Locator | DI Container | config/services.yaml |
| Event Dispatcher | Hooks de seguridad | BookController |
| Data Transformer | Serialización | Serializer groups |
