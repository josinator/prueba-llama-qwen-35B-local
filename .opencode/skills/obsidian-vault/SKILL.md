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
3. **Al completar**: Generar grafo con graphify para visualizar conexiones
4. **Referencia**: Leer notas del vault para contexto antes de programar

## Integración con Graphify

```bash
# Generar grafo de conocimiento
npx graphify obsidian-vault/ --output obsidian-vault/graph.html

# Visualizar conexiones entre notas
npx graphify obsidian-vault/ --format mermaid --output obsidian-vault/connections.mmd
```
