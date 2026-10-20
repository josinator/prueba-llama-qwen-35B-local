# Book API - Symfony PHP 8.3 + MariaDB + Docker

API REST para gestión de libros construida con **Symfony 7.4** usando Docker Compose.

## Stack Tecnológico

- **PHP 8.3** (FPM)
- **MariaDB 10.11**
- **Nginx** (proxy inverso)
- **Docker Compose**

## Requisitos

- [Docker](https://docs.docker.com/get-docker/) instalado (v20.10+)
- [Docker Compose](https://docs.docker.com/compose/install/) integrado con Docker CLI
- [Make](https://www.gnu.org/software/make/) (opcional, para usar Makefile)

## Levantamiento Rápido

```bash
# 1. Clonar el proyecto (si aplica)
git clone <repositorio>
cd <nombre-del-proyecto>

# 2. Levantar todos los contenedores y construir imágenes
make dev

# 3. Instalar dependencias PHP/Composer
make vendor

# 4. Aplicar migraciones de base de datos
make db-migrate
```

El servidor estará disponible en: **http://localhost:8080**

## Comandos Makefile

### Docker

| Comando | Descripción |
|---------|-------------|
| `make up` | Levanta todos los contenedores |
| `make down` | Detiene y elimina contenedores |
| `make restart` | Reinicia todos los servicios |
| `make build` | Reconstruye imágenes Docker |
| `make clean` | Elimina contenedores, volumes y redes |
| `make logs` | Muestra logs en tiempo real |

### Desarrollo

| Comando | Descripción |
|---------|-------------|
| `make dev` | Construye e inicia todo el proyecto |
| `make shell` | Entra al shell del contenedor PHP |
| `make symfony [cmd]` | Ejecuta comandos de Symfony (ej: `make symfony list:routes`) |
| `make composer [cmd]` | Ejecuta Composer (ej: `make composer require doctrine/annotations`) |
| `make vendor` | Instala dependencias (`composer install`) |

### Base de Datos

| Comando | Descripción |
|---------|-------------|
| `make db-migrate` | Aplica migraciones Doctrine |
| `make db-reset` | Elimina y recrea la base de datos |
| `make db-dump` | Exporta backup SQL a `./dumps/` |

## Endpoints de la API

Base URL: `http://localhost:8080/api/books`

| Método | Ruta | Descripción |
|--------|------|-------------|
| GET | `/api/books` | Listar todos los libros (soporta query `?title=xxx`) |
| GET | `/api/books/{id}` | Mostrar un libro por ID |
| POST | `/api/books` | Crear un nuevo libro |
| PUT | `/api/books/{id}` | Actualizar un libro existente |

### Ejemplo: Crear un libro

```bash
curl -X POST http://localhost:8080/api/books \
  -H "Content-Type: application/json" \
  -d '{
    "title": "El Principito",
    "author": "Antoine de Saint-Exupéry",
    "description": "Un clásico de la literatura mundial"
  }'
```

### Ejemplo: Buscar por título

```bash
curl http://localhost:8080/api/books?title=principito
```

> La búsqueda usa **hash MD5** del título normalizado (minúsculas, trim).

## Configuración

Variables de entorno en `.env` (se copian a los contenedores automáticamente):

| Variable | Valor por defecto | Descripción |
|----------|-------------------|-------------|
| `DATABASE_URL` | `mysql://symfony:symfony@database:3306/books?serverVersion=10.11` | Conexión a MariaDB |
| `APP_SECRET` | (generado) | Secreto de la aplicación Symfony |
| `PORT` | `8080` | Puerto expuesto por Nginx |

## Estructura del Proyecto

```
├── Makefile                        # Task runner principal
├── docker-compose.yml              # Configuración Docker
├── conf/
│   └── nginx/default.conf          # Proxy inverso
├── src/
│   ├── Entity/Book.php             # Entidad ORM con UUID + titleHash
│   ├── Controller/BookController.php # Controlador CRUD
│   └── Repository/BookRepository.php # Repositorio Doctrine
├── config/                         # Archivos de configuración Symfony
├── public/index.php                # Punto de entrada PHP
├── .env                            # Variables de entorno
└── dumps/                          # Backups de base de datos
```

## Respuesta de la API

### Crear libro (POST) → `201 Created`

```json
{
  "uuid": "a3f2b8c1-4d5e-6f7a-8b9c-0d1e2f3a4b5c",
  "title": "El Principito",
  "titleHash": "f5bb4f8c5d5b5e5f5a5b5c5d5e5f5a5b",
  "author": "Antoine de Saint-Exupéry",
  "description": "Un clásico de la literatura mundial",
  "createdAt": "2026-10-19T00:00:00+00:00"
}
```

### Listar libros (GET) → `200 OK`

```json
[
  {
    "uuid": "a3f2b8c1-4d5e-6f7a-8b9c-0d1e2f3a4b5c",
    "title": "El Principito",
    "titleHash": "f5bb4f8c5d5b5e5f5a5b5c5d5e5f5a5b",
    "author": "Antoine de Saint-Exupéry",
    "description": "Un clásico de la literatura mundial",
    "createdAt": "2026-10-19T00:00:00+00:00"
  }
]
```

## Notas Técnicas

- **UUID generado** en PHP con `random_bytes(16)` antes del `prePersist`.
- **Title Hash**: MD5(`strtolower(trim($title))`) calculado automáticamente.
- **Timestamps** `createdAt` y `updatedAt` se gestionan con Doctrine LifecycleCallbacks.

## Troubleshooting

### Error al conectar a la base de datos
```bash
make db-reset    # Reinicia schema
make db-migrate  # Re-aplica migraciones
```

### Limpiar todo y empezar desde cero
```bash
make clean       # Elimina volúmenes
make dev         # Reconstruye todo
make vendor      # Reconstruye dependencias
make db-migrate  # Re-crea esquema