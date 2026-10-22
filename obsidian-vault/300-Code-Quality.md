# Calidad de Código PHP

**Fecha:** 2026-10-22
**Estado:** Implementado

## Resumen

Se implementó un sistema de calidad de código PHP con tres componentes:

1. **PHPStan** — Análisis estático (Level 7)
2. **PHP-CS-Fixer** — Corrección automática de estilo
3. **Pre-commit Hook** — Validación automática antes de commits

## Configuración

### PHPStan
- **Archivo:** `phpstan.neon`
- **Nivel:** 7 (estricto)
- **Rutas:** src/, tests/
- **Bootstrap:** vendor/autoload.php

### PHP-CS-Fixer
- **Archivo:** `.php-cs-fixer.php`
- **Reglas:** PSR-12 + custom Symfony
- **Rutas:** src/, tests/, bin/, config/

### Pre-commit Hook
- **Archivo:** `.git/hooks/pre-commit`
- **Comportamiento:** Bloquea commits con código de mala calidad

## Comandos

```bash
make phpstan      # Análisis estático
make cs-fixer     # Corrige estilo
make cs-fixer-dry # Verifica sin corregir
make quality      # PHPStan + CS-Fixer
```

## Agentes y Skills

- **Agente:** `.opencode/agents/code-quality.md`
- **Skill:** `.opencode/skills/code-quality/SKILL.md`

## Docker

- **PHP:** Actualizado a 8.3.24 (parches de seguridad)
- **Imagen:** `php:8.3.24-fpm`
