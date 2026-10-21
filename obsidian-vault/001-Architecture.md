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
┌──────────────────────────────────────────────┐
│              ADAPTADORES                     │
│  ┌──────────────┐  ┌──────────────────────┐  │
│  │ Controllers  │  │   Repositories       │  │
│  │ (HTTP Input) │  │   (DB Output)        │  │
│  └──────────────┘  └──────────────────────┘  │
├──────────────────────────────────────────────┤
│            APLICACIÓN                        │
│  ┌──────────────────┐  ┌─────────────────┐  │
│  │ Service Interfaces│  │    DTOs         │  │
│  │ (Input Ports)    │  │ (Data Objects)  │  │
│  └──────────────────┘  └─────────────────┘  │
├──────────────────────────────────────────────┤
│               DOMINIO                        │
│  ┌──────────────┐  ┌──────────────────────┐  │
│  │   Entities   │  │   Value Objects      │  │
│  │ (Book, etc.) │  │ (Title, ISBN)        │  │
│  └──────────────┘  └──────────────────────┘  │
└──────────────────────────────────────────────┘
         ↑                    ↑
    Dependencias          Dependencias
    apuntan hacia         apuntan hacia
    adentro               adentro
```

### Reglas de Dependencia
1. Dominio → Ninguna dependencia externa
2. Aplicación → Dominio (interfaces)
3. Adaptadores → Aplicación (implementa interfaces)

### Archivos Clave
- `src/Entity/Book.php` — Ver [[010-Domain-Model]]
- `src/Service/BookServiceInterface.php` — Puerto de aplicación
- `src/Repository/BookRepository.php` — Adaptador DB
- `src/Controller/BookController.php` — Adaptador HTTP
- `src/DTO/BookDTO.php` — Objeto de transferencia

### Patrones Usados
Ver [[100-Patterns-Used]] para catálogo completo.

### Patrón de Inyección de Dependencias
```yaml
# config/services.yaml
services:
    App\Service\BookServiceInterface:
        class: App\Service\BookService
        arguments: ['@App\Port\BookRepositoryInterface']
    
    App\Port\BookRepositoryInterface:
        class: App\Repository\BookRepository
```
