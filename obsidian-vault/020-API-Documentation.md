---
type: api
tags: [api, endpoints, swagger]
---

# API Documentation — Book API

## Base URL
`http://localhost:8080/api`

## Endpoints

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
