---
type: api
tags: [api, endpoints, swagger]
---

# API Documentation — Book API

## Base URLs
- **REST API:** `http://localhost:8080/api`
- **Dashboard API:** `http://localhost:8080/dashboard/api`
- **SSE:** `http://localhost:8080/dashboard/sse`

## REST API Endpoints (`/api/books`)

### GET /books
Listar todos los libros.

**Query Parameters:**
- `title` — Filtrar por título (usa titleHash)

**Response:** `200 OK`
```json
[
  {
    "uuid": "A3F2B8C14D5E6F7A8B9C0D1E2F3A4B5C",
    "title": "El Principito",
    "author": "Antoine de Saint-Exupéry",
    "description": "Un clásico de la literatura mundial",
    "createdAt": "2026-10-19T00:00:00+00:00",
    "updatedAt": "2026-10-19T00:00:00+00:00"
  }
]
```

**Headers:**
- `Cache-Control: max-age=60`

### GET /books/{id}
Obtener libro por UUID.

**Response:** `200 OK` o `404 Not Found`

### POST /books
Crear nuevo libro.

**Body:**
```json
{
  "title": "El Principito",
  "author": "Antoine de Saint-Exupéry",
  "description": "Un clásico de la literatura mundial"
}
```

**Response:** `201 Created` o `400 Bad Request`

### PUT /books/{id}
Actualizar libro existente.

**Body:** Mismo formato que POST

**Response:** `200 OK`, `404 Not Found`, o `400 Bad Request`

## Dashboard API Endpoints (`/dashboard/api/`)

### GET /dashboard/api/stats
Obtener estadísticas generales.

**Response:** `200 OK`
```json
{
  "totalBooks": 150,
  "booksWithDescription": 120,
  "booksWithoutDescription": 30,
  "createdToday": 5,
  "updatedToday": 3,
  "authorsCount": 45,
  "oldestBookTitle": "Libro antiguo",
  "newestBookTitle": "Libro nuevo"
}
```

### GET /dashboard/api/search
Buscar libros con paginación.

**Query Parameters:**
- `q` — Query de búsqueda (obligatorio)
- `page` — Página (default: 1)
- `limit` — Resultados por página (default: 20)

**Response:** `200 OK`
```json
{
  "books": [
    {
      "id": "A3F2B8C1...",
      "title": "El Principito",
      "author": "Antoine de Saint-Exupéry",
      "description": "Un clásico...",
      "createdAt": "2026-10-19T00:00:00+00:00",
      "updatedAt": "2026-10-19T00:00:00+00:00"
    }
  ],
  "total": 150,
  "page": 1,
  "limit": 20,
  "totalPages": 8
}
```

### POST /dashboard/api/books
Crear libro desde dashboard.

**Body:**
```json
{
  "title": "El Principito",
  "author": "Antoine de Saint-Exupéry",
  "description": "Un clásico de la literatura mundial"
}
```

**Response:** `201 Created` o `422 Unprocessable Entity`

### GET /dashboard/api/books/{id}
Obtener libro por ID.

**Response:** `200 OK` o `404 Not Found`

### PUT /dashboard/api/books/{id}
Actualizar libro.

**Body:** Mismo formato que POST

**Response:** `200 OK`, `404 Not Found`, o `422 Unprocessable Entity`

### DELETE /dashboard/api/books/{id}
Eliminar libro.

**Response:** `200 OK` o `404 Not Found`
```json
{
  "success": true
}
```

## SSE Endpoint (`/dashboard/sse`)

Server-Sent Events para actualizaciones en tiempo real.

**Headers:**
- `Content-Type: text/event-stream`
- `Cache-Control: no-cache`
- `Connection: keep-alive`

## Error Responses

### 400 Bad Request
```json
{
  "errors": {
    "title": ["Este valor no debe estar vacío."]
  }
}
```

### 404 Not Found
```json
{
  "error": "Libro no encontrado"
}
```

### 422 Unprocessable Entity
```json
{
  "errors": {
    "title": ["El título es obligatorio."],
    "author": ["El autor es obligatorio."]
  }
}
```

### 429 Too Many Requests
```
Demasiadas solicitudes. Inténtalo nuevamente en un minuto.
```

## Security Headers
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `X-XSS-Protection: 1; mode=block`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy: camera=(), microphone=(), geolocation=()`
