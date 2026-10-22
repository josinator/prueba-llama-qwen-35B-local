---
name: code-quality
description: Guía completa de calidad de código PHP. Incluye configuración y uso de PHPStan Level 7, PHP-CS-Fixer, y el pre-commit hook. Usa para mantener código limpio, tipado correctamente, y libre de errores comunes.
---

# PHP Code Quality — Skill

## Herramientas de Calidad

### 1. PHPStan (Análisis Estático)

**Nivel:** 7 (estricto)
**Configuración:** `phpstan.neon`

```bash
# Ejecutar análisis
make phpstan

# O directamente
./vendor/bin/phpstan analyse --ansi

# O en Docker
docker compose exec app sh -c "php ./vendor/bin/phpstan analyse --ansi"
```

**Qué detecta (Level 7):**
- Parámetros sin tipo declarado
- Retornos sin tipo declarado
- Acceso a propiedades de objetos null
- Llamadas a métodos inexistentes
- Arrays sin claves verificadas
- Comparaciones incorrectas
- Código inalcanzable
- Tipos incompatibles

### 2. PHP-CS-Fixer (Estilo de Código)

**Configuración:** `.php-cs-fixer.php`
**Reglas:** PSR-12 + Symfony custom

```bash
# Corregir estilo automáticamente
make cs-fixer

# O directamente
./vendor/bin/php-cs-fixer fix --ansi

# Solo verificar sin corregir
make cs-fixer-dry

# O directamente
./vendor/bin/php-cs-fixer fix --dry-run --diff --ansi
```

**Qué corrige:**
- Imports no usados
- Imports no ordenados
- Sintaxis de arrays (short array)
- Comillas simples vs dobles
- Espacios en blanco
- Separación de elementos de clase
- Declaración de visibilidad
- Tipos de retorno

### 3. Pre-commit Hook

**Ubicación:** `.git/hooks/pre-commit`

Ejecuta automáticamente antes de cada commit:
1. PHP-CS-Fixer (dry-run)
2. PHPStan (analyse)

Si alguno falla, el commit se **bloquea**.

**Saltar el hook (NO recomendado):**
```bash
git commit --no-verify -m "message"
```

---

## Comandos Makefile

| Comando | Descripción |
|---------|-------------|
| `make phpstan` | Análisis estático PHPStan |
| `make cs-fixer` | Corrige estilo automáticamente |
| `make cs-fixer-dry` | Verifica estilo sin corregir |
| `make quality` | Ejecuta PHPStan + CS-Fixer |

---

## Checklist Pre-Commit

Antes de commitear código:

- [ ] `make cs-fixer-dry` pasa sin violations
- [ ] `make phpstan` pasa sin errores
- [ ] Código con tipos declarados (PHP 8.3+)
- [ ] No hay imports no usados
- [ ] Imports ordenados alfabéticamente
- [ ] No hay código con `@var` innecesarios

---

## Troubleshooting

### PHP-CS-Fixer no encontrado
```bash
make vendor  # Instala dependencias
```

### PHPStan no encontrado
```bash
make vendor  # Instala dependencias
```

### Errores persistentes de PHPStan
1. Revisa el tipo de cada parámetro y retorno
2. Añade `|null` donde sea necesario
3. Verifica que los métodos existen en las clases
4. Usa `?? null` en lugar de `isset()`

### Código estilo inconsistente
```bash
make cs-fixer  # Corrige automáticamente
```
