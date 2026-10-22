---
name: obsidian-vault
description: Gestiona el vault de Obsidian para conocimiento local del proyecto. Crea y organiza notas de arquitectura, decisiones técnicas, y documentación. Usa para guardar contexto del proyecto, patrones aplicados, y lecciones aprendidas.
---

# Obsidian Vault — Gestión de Conocimiento Local

## Propósito

Obsidian se usa como **memoria local del proyecto** para:
1. Documentar decisiones de arquitectura y diseño
2. Guardar contexto que no cabe en el prompt del LLM
3. Crear grafo de conocimiento con graphify
4. Reducir contexto consumido por la GPU/CPU del LLM

## Estructura del Vault

```
obsidian-vault/
├── README.md                    # Guía del vault
├── 000-Project-Overview.md      # Vista general del proyecto
├── 001-Architecture.md          # Arquitectura hexagonal aplicada
├── 002-Design-Decisions.md      # Decisiones de diseño (ADR)
├── 010-Domain-Model.md          # Modelo de dominio
├── 020-API-Documentation.md     # Documentación de API
├── 030-Services.md              # Servicios y sus interfaces
├── 040-Repository-Pattern.md    # Repositorios implementados
├── 050-Testing-Strategy.md      # Estrategia de testing
├── 100-Patterns-Used.md         # Patrones de diseño aplicados
├── 200-TODOs.md                 # Tareas pendientes
└── attachments/                 # Imágenes, diagrams, etc.
```

## Convenciones de Nombres

- Prefijo numérico para orden: `001-`, `002-`, etc.
- Snake case con guiones: `design-decisions.md`
- Tags: `#architecture`, `#decision`, `#domain`, `#api`, `#pattern`

## Notas de Decisión de Arquitectura (ADR)

```markdown
---
type: adr
status: accepted
date: 2026-10-20
---

# ADR-001: Uso de Arquitectura Hexagonal

## Contexto
Necesitamos separar el dominio de la infraestructura para facilitar tests y cambios tecnológicos.

## Decisión
Adoptaremos arquitectura hexagonal (ports & adapters) con:
- Puertos definidos como interfaces en `src/Service/`
- Adaptadores en `src/Controller/` y `src/Repository/`
- Dominio en `src/Entity/` y `src/ValueObject/`

## Consecuencias
- + Boilerplate inicial
- + Fácil de cambiar DB o framework
- + Tests unitarios sin infraestructura
- - Curva de aprendizaje para nuevos devs
```

## Flujo de Trabajo

1. **Antes de codificar**: Crear nota de arquitectura en `obsidian-vault/`
2. **Durante el desarrollo**: Actualizar notas con decisiones y patrones usados
3. **Al completar**: Actualizar grafo con `graphify update .` para mantenerlo sincronizado
4. **Referencia**: Usar `graphify query` para encontrar contexto relevante antes de programar

## Integración con Graphify

Graphify mantiene un grafo de conocimiento actualizado en `graphify-out/` que conecta:
- Código fuente (AST + relaciones)
- Notas del vault (Obsidian)
- Decisiones de arquitectura (ADR)

### Comandos principales

```bash
# Actualizar grafo después de cambios de código
graphify update .

# Consultar el grafo para encontrar contexto relevante
graphify query "¿cómo se implementa la autenticación?"

# Encontrar ruta entre dos archivos
graphify path "src/Controller/BookController.php" "src/Repository/BookRepository.php"

# Explicar un concepto del grafo
graphify explain "RateLimitingMiddleware"

# Ver nodos más conectados (hubs arquitectónicos)
graphify god-nodes --top 10

# Ver archivos afectados por un cambio
graphify affected "src/DTO/CreateBookDTO.php"
```

### Sincronización con Obsidian

```bash
# Generar visualización HTML del grafo completo
graphify tree --output graphify-out/GRAPH_TREE.html

# Ver reportes de comunidades
cat graphify-out/GRAPH_REPORT.md
```

### Uso con OpenCode

El plugin `tool.execute.before` ejecuta automáticamente `graphify query` antes de responder preguntas del códigobase, reduciendo el contexto necesario y mejorando velocidad/qualidad.
