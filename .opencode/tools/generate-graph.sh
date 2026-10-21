#!/bin/bash
# Genera grafo de conocimiento desde el vault de Obsidian
# Uso: ./generate-graph.sh

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
PROJECT_DIR="$(dirname "$(dirname "$SCRIPT_DIR")")"
VAULT_DIR="${PROJECT_DIR}/obsidian-vault"

echo "📚 Generando grafo de conocimiento..."
echo "   Vault: ${VAULT_DIR}"

# Generar grafo HTML interactivo
node "${SCRIPT_DIR}/knowledge-graph.js" "${VAULT_DIR}"

echo "✅ Grafo generado:"
echo "   - ${VAULT_DIR}/graph.html"
echo ""
echo "💡 Abre graph.html en tu navegador para ver el grafo interactivo"
