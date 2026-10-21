#!/usr/bin/env node
// Genera grafo de conocimiento desde vault de Obsidian
// Lee archivos .md, extrae links y tags, genera HTML interactivo

const fs = require('fs');
const path = require('path');

const vaultDir = (process.argv[2] && !process.argv[2].startsWith('-')) ? process.argv[2] : path.join(__dirname, '../../obsidian-vault');

// Leer todos los archivos .md del vault
const files = fs.readdirSync(vaultDir).filter(f => f.endsWith('.md'));

const nodes = [];
const links = [];

files.forEach(file => {
  const filePath = path.join(vaultDir, file);
  const content = fs.readFileSync(filePath, 'utf8');
  const name = file.replace('.md', '');
  
  // Extraer tags del frontmatter
  const tagsMatch = content.match(/tags:\s*\[([^\]]+)\]/);
  const tags = tagsMatch ? tagsMatch[1].split(',').map(t => t.trim()) : [];
  
  // Extraer wikilinks [[...]]
  const wikilinks = content.match(/\[\[([^\]]+)\]\]/g) || [];
  const linkTargets = wikilinks.map(l => l.replace(/\[\[|\]\]/g, ''));
  
  // Extraer links markdown [...](...)
  const markdownLinks = content.match(/\[([^\]]+)\]\(([^\)]+)\)/g) || [];
  
  nodes.push({
    id: name,
    file: file,
    tags: tags,
    links: linkTargets,
    size: 10 + tags.length * 3
  });
  
  linkTargets.forEach(target => {
    links.push({
      source: name,
      target: target
    });
  });
});

// Generar HTML
const html = `<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Grafo de Conocimiento — Book API</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: #1a1b26;
      color: #a9b1d6;
      overflow: hidden;
    }
    #canvas {
      width: 100vw;
      height: 100vh;
      cursor: grab;
    }
    #canvas:active { cursor: grabbing; }
    #legend {
      position: fixed;
      top: 20px;
      left: 20px;
      background: rgba(26, 27, 38, 0.9);
      border: 1px solid #292e42;
      border-radius: 8px;
      padding: 15px;
      font-size: 12px;
      z-index: 100;
    }
    #legend h3 { color: #7aa2f7; margin-bottom: 10px; }
    .legend-item { display: flex; align-items: center; margin: 5px 0; }
    .legend-dot { width: 10px; height: 10px; border-radius: 50%; margin-right: 8px; }
    #info {
      position: fixed;
      bottom: 20px;
      left: 20px;
      background: rgba(26, 27, 38, 0.9);
      border: 1px solid #292e42;
      border-radius: 8px;
      padding: 15px;
      max-width: 300px;
      display: none;
      z-index: 100;
    }
    #info h4 { color: #7aa2f7; margin-bottom: 5px; }
    #info .tags { color: #9ece6a; font-size: 11px; }
    #stats {
      position: fixed;
      top: 20px;
      right: 20px;
      background: rgba(26, 27, 38, 0.9);
      border: 1px solid #292e42;
      border-radius: 8px;
      padding: 15px;
      font-size: 12px;
      z-index: 100;
    }
    #stats h3 { color: #7aa2f7; margin-bottom: 10px; }
    #stats p { margin: 3px 0; }
  </style>
</head>
<body>
  <div id="legend">
    <h3>Tags</h3>
    ${[...new Set(nodes.flatMap(n => n.tags))].map(tag => {
      const colors = {
        'architecture': '#7aa2f7',
        'domain': '#9ece6a',
        'api': '#e0af68',
        'pattern': '#bb9af7',
        'decision': '#f7768e',
        'todo': '#7dcfff',
        'overview': '#73daca'
      };
      return `<div class="legend-item">
        <div class="legend-dot" style="background:${colors[tag] || '#7aa2f7'}"></div>
        <span>${tag}</span>
      </div>`;
    }).join('')}
  </div>
  
  <div id="stats">
    <h3>Estadísticas</h3>
    <p>📄 Notas: ${nodes.length}</p>
    <p>🔗 Conexiones: ${links.length}</p>
    <p>🏷️ Tags: ${[...new Set(nodes.flatMap(n => n.tags))].length}</p>
  </div>
  
  <div id="info">
    <h4 id="info-title"></h4>
    <p class="tags" id="info-tags"></p>
  </div>
  
  <canvas id="canvas"></canvas>
  
  <script>
    const nodesData = ${JSON.stringify(nodes, null, 2)};
    const linksData = ${JSON.stringify(links, null, 2)};
    
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');
    const info = document.getElementById('info');
    
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    
    // Colores por tag
    const tagColors = {
      'architecture': '#7aa2f7',
      'domain': '#9ece6a',
      'api': '#e0af68',
      'pattern': '#bb9af7',
      'decision': '#f7768e',
      'todo': '#7dcfff',
      'overview': '#73daca'
    };
    
    // Posiciones iniciales
    const centerX = canvas.width / 2;
    const centerY = canvas.height / 2;
    const radius = Math.min(canvas.width, canvas.height) * 0.3;
    
    nodesData.forEach((node, i) => {
      const angle = (2 * Math.PI * i) / nodesData.length - Math.PI / 2;
      node.x = centerX + radius * Math.cos(angle);
      node.y = centerY + radius * Math.sin(angle);
      node.vx = 0;
      node.vy = 0;
      node.primaryTag = node.tags[0] || 'overview';
    });
    
    // Colisiones para evitar superposición
    function resolveCollisions() {
      const minDist = 80;
      for (let i = 0; i < nodesData.length; i++) {
        for (let j = i + 1; j < nodesData.length; j++) {
          const dx = nodesData[j].x - nodesData[i].x;
          const dy = nodesData[j].y - nodesData[i].y;
          const dist = Math.sqrt(dx * dx + dy * dy);
          if (dist < minDist && dist > 0) {
            const force = (minDist - dist) / minDist * 0.1;
            const fx = (dx / dist) * force;
            const fy = (dy / dist) * force;
            nodesData[i].vx -= fx;
            nodesData[i].vy -= fy;
            nodesData[j].vx += fx;
            nodesData[j].vy += fy;
          }
        }
      }
    }
    
    // Atracción por conexiones
    function applyForces() {
      linksData.forEach(link => {
        const source = nodesData.find(n => n.id === link.source);
        const target = nodesData.find(n => n.id === link.target);
        if (!source || !target) return;
        
        const dx = target.x - source.x;
        const dy = target.y - source.y;
        const dist = Math.sqrt(dx * dx + dy * dy) || 1;
        const idealDist = 150;
        const force = (dist - idealDist) * 0.01;
        
        const fx = (dx / dist) * force;
        const fy = (dy / dist) * force;
        
        source.vx += fx;
        source.vy += fy;
        target.vx -= fx;
        target.vy -= fy;
      });
      
      // Repulsión general
      nodesData.forEach((node, i) => {
        nodesData.forEach((other, j) => {
          if (i === j) return;
          const dx = node.x - other.x;
          const dy = node.y - other.y;
          const dist = Math.sqrt(dx * dx + dy * dy) || 1;
          if (dist < 100) {
            node.vx += (dx / dist) * 0.5;
            node.vy += (dy / dist) * 0.5;
          }
        });
      });
    }
    
    // Renderizado
    function draw() {
      ctx.fillStyle = '#1a1b26';
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      
      // Dibujar conexiones
      linksData.forEach(link => {
        const source = nodesData.find(n => n.id === link.source);
        const target = nodesData.find(n => n.id === link.target);
        if (!source || !target) return;
        
        ctx.beginPath();
        ctx.moveTo(source.x, source.y);
        ctx.lineTo(target.x, target.y);
        ctx.strokeStyle = 'rgba(122, 162, 247, 0.2)';
        ctx.lineWidth = 1;
        ctx.stroke();
      });
      
      // Dibujar nodos
      nodesData.forEach(node => {
        const color = tagColors[node.primaryTag] || '#7aa2f7';
        
        // Glow
        ctx.beginPath();
        ctx.arc(node.x, node.y, node.size + 5, 0, Math.PI * 2);
        ctx.fillStyle = color + '33';
        ctx.fill();
        
        // Nodo
        ctx.beginPath();
        ctx.arc(node.x, node.y, node.size, 0, Math.PI * 2);
        ctx.fillStyle = color;
        ctx.fill();
        ctx.strokeStyle = '#1a1b26';
        ctx.lineWidth = 2;
        ctx.stroke();
        
        // Label
        ctx.fillStyle = '#a9b1d6';
        ctx.font = '11px -apple-system, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(node.id.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase()), node.x, node.y + node.size + 15);
      });
      
      // Física
      resolveCollisions();
      applyForces();
      nodesData.forEach(node => {
        node.x += node.vx;
        node.y += node.vy;
        node.vx *= 0.9;
        node.vy *= 0.9;
        
        // Mantener dentro del canvas
        node.x = Math.max(50, Math.min(canvas.width - 50, node.x));
        node.y = Math.max(50, Math.min(canvas.height - 50, node.y));
      });
      
      requestAnimationFrame(draw);
    }
    
    // Interacción con mouse
    let dragging = null;
    canvas.addEventListener('mousedown', e => {
      const rect = canvas.getBoundingClientRect();
      const mx = e.clientX - rect.left;
      const my = e.clientY - rect.top;
      
      for (const node of nodesData) {
        const dx = mx - node.x;
        const dy = my - node.y;
        if (Math.sqrt(dx * dx + dy * dy) < node.size + 5) {
          dragging = node;
          // Mostrar info
          document.getElementById('info-title').textContent = node.id.replace(/-/g, ' ');
          document.getElementById('info-tags').textContent = node.tags.join(', ') || 'Sin tags';
          info.style.display = 'block';
          break;
        }
      }
    });
    
    canvas.addEventListener('mousemove', e => {
      if (dragging) {
        const rect = canvas.getBoundingClientRect();
        dragging.x = e.clientX - rect.left;
        dragging.y = e.clientY - rect.top;
        dragging.vx = 0;
        dragging.vy = 0;
      }
    });
    
    canvas.addEventListener('mouseup', () => { dragging = null; });
    
    // Resize
    window.addEventListener('resize', () => {
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;
    });
    
    draw();
  </script>
</body>
</html>`;

// Escribir archivo
const outputPath = path.join(vaultDir, 'graph.html');
fs.writeFileSync(outputPath, html, 'utf8');
console.log(`✅ Grafo generado: ${outputPath}`);
console.log(`   📄 ${nodes.length} notas`);
console.log(`   🔗 ${links.length} conexiones`);
