---
type: overview
project: Book API
stack: "PHP 8.3, Symfony 7.1, MariaDB 10.11, Nginx, Docker"
date: 2026-10-20
---

# Book API — Project Overview

## Objetivo
API REST para gestión de libros con arquitectura hexagonal.

## Stack Tecnológico
- **PHP** 8.3+
- **Symfony** 7.1 (Framework)
- **MariaDB** 10.11 (Base de datos)
- **Nginx** (Proxy inverso)
- **Docker Compose** (Orquestación)

## Arquitectura
Véase [[001-Architecture]] para detalles.

Arquitectura Hexagonal (Ports & Adapters):
```
Dominio (Entity, ValueObject)
    ↑
Aplicación (Service Interface, DTO)
    ↑
Adaptadores (Controller, Repository)
```

## Endpoints Actuales
| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/books` | Listar libros (cache 60s) |
| GET | `/api/books/{id}` | Obtener libro |
| POST | `/api/books` | Crear libro |
| PUT | `/api/books/{id}` | Actualizar libro |

## Features Implementadas
- Rate limiting (100 req/min/IP)
- Security headers (5 headers)
- HTTP Cache en listados
- DTO Groups para serialización
- UUID como primary key
- Title hash MD5 para búsquedas
- Lifecycle callbacks para timestamps

## Estado
- [x] Infraestructura Docker funcionando
- [x] API CRUD básica
- [x] Rate limiting
- [x] Security headers
- [x] HTTP Cache
- [x] DTOs con validación
- [ ] Tests unitarios completos
- [ ] Value Objects (Title, ISBN)
- [ ] Domain Events
- [ ] Search avanzado
