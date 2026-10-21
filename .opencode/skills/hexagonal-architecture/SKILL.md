---
name: hexagonal-architecture
description: Guía completa de Programación Hexagonal (Ports & Adapters) para PHP/Symfony. Define puertos de dominio, adaptadores de infraestructura, y separación clara de capas. Usa cuando diseñes arquitectura, crees services, entities, o refactorices código para mejor separación de responsabilidades.
---

# Programación Hexagonal — Ports & Adapters

## Filosofía

La arquitectura hexagonal (también llamada Ports & Adapters) separa el **núcleo de dominio** de la **infraestructura**. La aplicación es un hexágono donde:

```
┌─────────────────────────────────────────┐
│         DOMINIO (Core)                  │
│  ┌───────────────────────────────────┐  │
│  │  Entidades + Value Objects        │  │
│  │  Reglas de negocio                │  │
│  │  Interfaces de puertos (ports)    │  │
│  │                                   │  │
│  │         ▲                         │  │
│  │         │ depende de              │  │
│  │         │                         │  │
│  └─────────┼─────────────────────────┘  │
│            │                           │  │
│  ┌─────────▼─────────────────────────┐  │
│  │       ADAPTADORES (Infra)         │  │
│  │  HTTP Controllers (Input)         │  │
│  │  Database Repositories (Output)   │  │
│  │  External APIs (Output)           │  │
│  │  Message Queues (Output)          │  │
│  └───────────────────────────────────┘  │
└─────────────────────────────────────────┘
```

## Reglas Fundamentales

1. **El dominio NO conoce infraestructura** — nunca use `Doctrine`, `Symfony`, `MySQL` dentro de entidades o services de dominio
2. **Las dependencias apuntan hacia adentro** — controllers importan services, services importan repositories (interfaces), repositories implementan interfaces
3. **Los puertos son interfaces** — definen qué hace la infraestructura, no cómo lo hace
4. **Los adaptadores son concretos** — implementan los puertos con tecnología específica

## Capas de la Arquitectura

### Capa 1: Dominio (Domain Layer)
```
src/
├── Entity/              # Entidades de dominio
├── ValueObject/         # Value Objects inmutables
└── DomainEvent/         # Eventos de dominio (opcional)
```

**Responsabilidades:**
- Modelar el dominio del negocio
- Encapsular reglas de negocio
- Ser independientes de frameworks

### Capa 2: Aplicación (Application Layer)
```
src/
├── Service/             # Interfaces de servicio (puertos)
├── DTO/                 # Data Transfer Objects
└── Message/             # Command/Query objects
```

**Responsabilidades:**
- Orquestar flujo de aplicación
- Coordinar entre puertos de entrada y salida
- Validar datos de entrada

### Capa 3: Adaptadores (Adapter Layer)
```
src/
├── Controller/          # Adaptadores HTTP (entrada)
├── Repository/          # Adaptadores DB (salida)
└── External/            # Adaptadores a APIs externas (salida)
```

**Responsabilidades:**
- Traducir protocolos (HTTP, DB, etc.) a llamadas al dominio
- Implementar interfaces definidas en capa de aplicación

## Patrón: Puerto de Entrada (Input Port)

Un puerto de entrada define una operación que el sistema expone:

```php
<?php
namespace App\Service;

use App\DTO\BookDTO;
use App\Entity\Book;

/**
 * Puerto de entrada: operaciones que el sistema expone
 */
interface BookApplicationServiceInterface
{
    public function listBooks(): array;
    public function getBook(string $id): ?Book;
    public function createBook(BookDTO $dto): Book;
    public function updateBook(string $id, BookDTO $dto): ?Book;
    public function deleteBook(string $id): bool;
}
```

## Patrón: Puerto de Salida (Output Port)

Un puerto de salida define cómo el dominio interactúa con infraestructura:

```php
<?php
namespace App\Port;

use App\Entity\Book;

/**
 * Puerto de salida: cómo el dominio persiste datos
 */
interface BookRepositoryInterface
{
    public function save(Book $book): void;
    public function delete(Book $book): void;
    public function find(string $id): ?Book;
    public function findAll(): array;
    public function findByTitle(string $title): array;
}
```

## Patrón: Implementación del Adaptador

```php
<?php
namespace App\Repository;

use App\Entity\Book;
use App\Port\BookRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Adaptador: implementación concreta del puerto de salida
 */
class BookRepository extends ServiceEntityRepository implements BookRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function save(Book $book): void
    {
        $this->_em->persist($book);
        $this->_em->flush();
    }

    public function delete(Book $book): void
    {
        $this->_em->remove($book);
        $this->_em->flush();
    }

    public function find(string $id): ?Book
    {
        return $this->find($id);
    }

    public function findAll(): array
    {
        return $this->findAll();
    }

    public function findByTitle(string $title): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.titleHash = :hash')
            ->setParameter('hash', md5(strtolower(trim($title))))
            ->getQuery()
            ->getResult();
    }
}
```

## Patrón: Implementación del Servicio (Aplicación)

```php
<?php
namespace App\Service;

use App\DTO\BookDTO;
use App\Entity\Book;
use App\Port\BookRepositoryInterface;

/**
 * Adaptador: implementación del puerto de aplicación
 */
class BookService implements BookApplicationServiceInterface
{
    public function __construct(
        private BookRepositoryInterface $bookRepository,
    ) {}

    public function listBooks(): array
    {
        return $this->bookRepository->findAll();
    }

    public function getBook(string $id): ?Book
    {
        return $this->bookRepository->find($id);
    }

    public function createBook(BookDTO $dto): Book
    {
        // Validación de dominio
        if ($this->bookRepository->findByTitle($dto->title)) {
            throw new \DomainException('Ya existe un libro con ese título');
        }

        $book = new Book();
        $book->setTitle($dto->title);
        $book->setAuthor($dto->author);
        $book->setDescription($dto->description);

        $this->bookRepository->save($book);
        return $book;
    }

    public function updateBook(string $id, BookDTO $dto): ?Book
    {
        $book = $this->bookRepository->find($id);
        if (!$book) {
            return null;
        }

        $book->setTitle($dto->title);
        $book->setAuthor($dto->author);
        $book->setDescription($dto->description);

        $this->bookRepository->save($book);
        return $book;
    }

    public function deleteBook(string $id): bool
    {
        $book = $this->bookRepository->find($id);
        if (!$book) {
            return false;
        }

        $this->bookRepository->delete($book);
        return true;
    }
}
```

## Patrón: Controller como Adaptador HTTP

```php
<?php
namespace App\Controller;

use App\DTO\BookDTO;
use App\Service\BookApplicationServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Adaptador HTTP: traduce peticiones HTTP a llamadas al puerto de aplicación
 */
#[Route('/api/books', name: 'app_book_')]
class BookController extends AbstractController
{
    public function __construct(
        private BookApplicationServiceInterface $bookService,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json($this->bookService->listBooks());
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $book = $this->bookService->getBook($id);
        if (!$book) {
            return $this->json(['error' => 'Not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($book);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $dto = BookDTO::fromArray($data);
        $book = $this->bookService->createBook($dto);
        return $this->json($book, Response::HTTP_201_CREATED);
    }
}
```

## Value Objects (Inmutables)

Los Value Objects son objetos inmutables que se comparan por su valor, no por identidad:

```php
<?php
namespace App\ValueObject;

readonly class Title
{
    public function __construct(
        public string $value,
    ) {
        if (empty(trim($value))) {
            throw new \InvalidArgumentException('Title cannot be empty');
        }
        if (strlen($value) > 255) {
            throw new \InvalidArgumentException('Title too long');
        }
    }

    public function hash(): string
    {
        return md5(strtolower($this->value));
    }

    public function __toString(): string
    {
        return $this->value;
    }
}

readonly class ISBN
{
    public function __construct(
        public string $value,
    ) {
        if (!preg_match('/^(?:ISBN(?:-1[03])?:? )?(?=[0-9X]{10}$|^(?:978|979)[0-9]{10}$|^(?:978|979)[0-9]{13}$|^(?:978|979)[0-9]{10}$)$/', $value)) {
            throw new \InvalidArgumentException('Invalid ISBN format');
        }
    }

    public function normalize(): string
    {
        return strtoupper(preg_replace('/[- ]/', '', $this->value));
    }
}
```

## Patrón: Domain Events

Para desacoplar efectos secundarios:

```php
<?php
namespace App\DomainEvent;

final class BookCreatedEvent
{
    public function __construct(
        public readonly string $bookId,
        public readonly string $title,
        public readonly \DateTimeImmutable $createdAt,
    ) {}
}

// En el service:
$event = new BookCreatedEvent($book->uuid, $book->title, new \DateTimeImmutable());
// Disparar evento para notificaciones, indexación, etc.
```

## Dependency Injection Container (Symfony)

En `config/services.yaml`:

```yaml
services:
    # Puertos (interfaces) — Symfony los resuelve a implementaciones
    App\Service\BookApplicationServiceInterface:
        class: App\Service\BookService
        arguments:
            - '@App\Port\BookRepositoryInterface'

    App\Port\BookRepositoryInterface:
        class: App\Repository\BookRepository
        arguments:
            - '@doctrine.orm.entity_manager'

    # Controllers automáticamente registrados
    App\Controller\:
        resource: '../src/Controller/'
        tags: ['controller.service_arguments']
```

## Checklist de Arquitectura Hexagonal

- [ ] Dominio no importa frameworks (Doctrine, Symfony, etc.)
- [ ] Interfaces definidas en capa de aplicación
- [ ] Implementaciones en capa de adaptadores
- [ ] Dependency Inversion Principle (DIP) cumplido
- [ ] Controllers delgados (solo orquestación)
- [ ] Value Objects para datos complejos
- [ ] Entidades con comportamiento de dominio
- [ ] Tests del dominio sin infraestructura
- [ ] Fácil de cambiar tecnología (DB, framework, etc.)

## Anti-patrones a Evitar

| Anti-patrón | Correcto |
|-------------|----------|
| Entity usa `EntityManager` | Entity es puro, Repository maneja DB |
| Service conoce `BookRepository` concreto | Service conoce `BookRepositoryInterface` |
| Controller contiene lógica de negocio | Controller solo orquesta, Service contiene lógica |
| DTO con lógica compleja | DTO es solo datos, validación en service |
| Entity con `#[Route]` | Solo Controllers tienen routing attributes |
