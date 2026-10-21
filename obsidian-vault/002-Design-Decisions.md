---
type: adr
status: accepted
date: 2026-10-20
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
