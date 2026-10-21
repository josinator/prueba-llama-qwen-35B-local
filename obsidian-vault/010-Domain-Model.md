---
type: domain
tags: [domain, entity, value-object]
---

# Modelo de Dominio — Book API

## Entidades

### Book
```
Book
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

## Value Objects (Por Implementar)

### Title
```
Title
├── value: string
└── hash(): string
```
**Invariants:**
- No vacío
- Máximo 255 caracteres

### ISBN
```
ISBN
├── value: string
└── normalize(): string
```
**Invariants:**
- Formato ISBN-10 o ISBN-13 válido

## Relaciones
```
Book 1..1 → Title (Value Object)
Book 1..1 → Author (Value Object - future)
```
