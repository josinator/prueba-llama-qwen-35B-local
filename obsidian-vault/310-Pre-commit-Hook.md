# Pre-commit Hook

**Fecha:** 2026-10-22
**Estado:** Implementado

## Propósito

Garantizar que TODO el código commiteado pase validaciones de calidad, sin importar si lo hace un humano o un agente de IA.

## Cómo Funciona

El hook se ejecuta automáticamente antes de cada `git commit`:

1. **PHP-CS-Fixer (dry-run)** — Verifica estilo de código
2. **PHPStan (analyse)** — Verifica análisis estático

Si alguno falla, el commit se **bloquea** con mensajes de error claros.

## Comandos de Corrección

Si el commit se bloquea:

```bash
# Corregir estilo de código
make cs-fixer

# Ver errores de PHPStan
make phpstan

# Corregir errores manualmente
# (PHPStan Level 7 requiere tipos en todos los parámetros/retornos)
```

## Saltar el Hook

Para casos excepcionales (debug, hotfix urgente):

```bash
git commit --no-verify -m "message"
```

**Nota:** Esto NO se recomienda y solo debe usarse en situaciones excepcionales.

## Integración con Agentes IA

Los agentes de IA (como `code-quality`) también ejecutan estos validadores automáticamente antes de aprobar cambios, proporcionando una doble capa de protección.

## Referencias

- [PHPStan Documentation](https://phpstan.org/)
- [PHP-CS-Fixer Documentation](https://cs.symfony.com/)
- Skill: `.opencode/skills/code-quality/SKILL.md`
- Agente: `.opencode/agents/code-quality.md`
