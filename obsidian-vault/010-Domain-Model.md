---
type: domain
tags: [domain, entity, value-object]
---

# Modelo de Dominio — Book API

## Entidades

### Book (Dominio)
```
src/Domain/Entity/Book.php
```
```
Book (Domain Entity)
├── uuid: string (UUID primary key)
├── titleHash: string (MD5 index)
├── title: string
├── author: string
├── description: string|null
├── createdAt: DateTimeImmutable
└── updatedAt: DateTimeImmutable
```

**Reglas de negocio:**
- Título no puede estar vacío
- Título máximo 255 caracteres
- Author máximo 255 caracteres
- Description máximo 1000 caracteres
- createdAt se establece al crear
- updatedAt se actualiza en cada cambio

### Book (Infrastructure Entity)
```
src/Infrastructure/Entity/Book.php
```
Entidad Doctrine ORM que mapea la tabla de base de datos. Separa las anotaciones/atributos ORM de la lógica de dominio.

## Value Objects

### Title
```
src/Domain/ValueObject/Title.php
```
```
Title
├── value: string
└── hash(): string
```
**Invariants:**
- No vacío
- Máximo 255 caracteres
- Normalizado (trim + minúsculas para hash)

## Domain Events

### BookCreatedEvent
```
src/Domain/DomainEvent/BookCreatedEvent.php
```
Se dispara cuando se crea un nuevo libro. Contiene el UUID del libro y el timestamp.

### BookUpdatedEvent
```
src/Domain/DomainEvent/BookUpdatedEvent.php
```
Se dispara cuando se actualiza un libro existente. Contiene el UUID del libro y el timestamp.

### DomainEventListener
```
src/Infrastructure/EventListener/DomainEventListener.php
```
Adapter que escucha los Domain Events y ejecuta acciones en infraestructura (logs, notificaciones, etc.).

## Relaciones
```
Book 1..1 → Title (Value Object)
Book 1..* → BookCreatedEvent (Domain Event - on create)
Book 1..* → BookUpdatedEvent (Domain Event - on update)
```
