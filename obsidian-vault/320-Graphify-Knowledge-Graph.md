---
type: documentation
status: active
date: 2026-10-22
tags: [graphify, knowledge-graph, ai, optimization]
---

# Graphify — Grafo de Conocimiento del Proyecto

## Propósito

Graphify crea un grafo de conocimiento que conecta el código fuente con la documentación, permitiendo:

1. **Minimizar contexto**: Solo enviar al LLM las partes relevantes del código
2. **Ahorrar tokens**: Evitar enviar todo el proyecto en cada consulta
3. **Mejorar velocidad**: Consultas específicas en lugar de análisis completo
4. **Mejorar calidad**: Contexto preciso y relevante para cada tarea

## Instalación

Graphify está instalado como herramienta global via `uv`:

```bash
# Instalación (ya realizada)
uv tool install "graphifyy[mcp]==0.9.32"
```

Se encuentra en `~/.local/bin/graphify`.

## Integración con OpenCode

- **Plugin**: `.opencode/plugins/graphify.js` se ejecuta antes de cada herramienta
- **AGENTS.md**: Sección que instruye usar `graphify query` para consultas del código
- **Obsidian**: Skill actualizada para trabajar con el grafo

## Archivos Generados

```
graphify-out/
├── graph.json           # Grafo principal (262 nodos, 371 edges)
├── GRAPH_REPORT.md      # Reporte de comunidades y arquitectura
├── graph.html           # Visualización interactiva
├── manifest.json        # Manifiesto de archivos analizados
├── .graphify_analysis.json
└── cache/               # Cache de extracción
```

## Comandos Principales

### Actualizar grafo

```bash
# Después de modificar código (AST-only, sin API)
graphify update .

# O via Makefile
make graphify-update
```

### Consultar grafo

```bash
# Buscar contexto relevante
graphify query "¿cómo se implementa la validación de libros?"
make graphify-query "validación de libros"

# Encontrar ruta entre archivos
graphify path "src/Controller/BookController.php" "src/Repository/BookRepository.php"
make graphify-path "src/Controller/BookController.php" "src/Repository/BookRepository.php"

# Explicar un concepto
graphify explain "RateLimitingMiddleware"
make graphify-explain "RateLimitingMiddleware"
```

### Análisis arquitectónico

```bash
# Ver nodos más conectados (hubs)
graphify god-nodes --top 10
make graphify-god-nodes

# Ver archivos afectados por un cambio
graphify affected "src/DTO/CreateBookDTO.php"
make graphify-affected "src/DTO/CreateBookDTO.php"

# Ver reporte completo
cat graphify-out/GRAPH_REPORT.md
make graphify-report
```

## Flujo de Trabajo

1. **Antes de consultar el código**: Usar `graphify query` para contexto relevante
2. **Después de modificar código**: Ejecutar `graphify update .`
3. **Para entender arquitectura**: Leer `GRAPH_REPORT.md` o usar `graphify god-nodes`
4. **Para impact analysis**: Usar `graphify affected` antes de refactorizar

## Beneficios

| Antes | Después |
|-------|---------|
| Enviar todo el proyecto al LLM | Solo contexto relevante |
| 100k+ tokens por consulta | 1-5k tokens por consulta |
| Respuestas genéricas | Respuestas contextualizadas |
| Análisis lento | Búsqueda instantánea |

## Sincronización con Obsidian

Las notas del vault pueden referenciar nodos del grafo:

```markdown
## Relación con el código

- **Implementación**: [[src/Controller/BookController.php]]
- **Grafo**: graphify query "BookController"
- **Patrón**: Controller como adapter de entrada
```

## Configuración

Variables de entorno opcionales:

```bash
GRAPHIFY_OUT=.ai/graphify-out  # Custom output directory
GRAPHIFY_FORCE=1               # Forzar re-extracción completa
```

## Notas Técnicas

- **Extracción**: AST + análisis semántico (opcional con LLM)
- **Clustering**: Agrupación automática en comunidades (22 detectadas)
- **Incremental**: Solo procesa archivos modificados desde última extracción
- **Code-only**: Modo sin API keys para extracción 100% local
