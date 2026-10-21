---
name: php-symfony
description: Guía experta para PHP 8.3+ y Symfony 7. Incluye mejores prácticas, DTOs, validación, testing con PHPUnit, Docker, y patrones específicos de Symfony. Usa cuando escribas código PHP, controladores, entities, services, o tests.
---

# PHP & Symfony 7 — Skill de Desarrollo

## Principios Generales

1. **PHP 8.3+ Moderno**: Usa typed properties, readonly classes, match expressions, named arguments, constructor property promotion, enums, and union types.
2. **Symfony 7.1+**: Usa attributes (#[Attribute]) en lugar de annotations. Symfony 7 es la versión LTS actual.
3. **Composer Autoloading**: PSR-4 con namespace `App\` mapeado a `src/`.
4. **Dependency Injection**: Inyección explícita en constructores. Nunca uses `container` directamente.

## Estructura Hexagonal de Archivos

```
src/
├── Controller/          # Adaptadores HTTP (entrada)
├── Entity/              # Dominio (entidades Doctrine)
├── Service/             # Aplicación + dominio (puertos)
├── DTO/                 # Data Transfer Objects
├── ValueObject/         # Value Objects inmutables
├── Repository/          # Adaptadores de infraestructura (DB)
└── Kernel.php
```

## Controladores (Adaptadores HTTP)

### Reglas
- Controladores son **delgados** — solo orquestan, no contienen lógica de negocio
- Siempre inyecta services via constructor
- Usa attributes de Symfony Router: `#[Route]`, `#[Method]`, `#[Cache]`
- Retorna `JsonResponse` para APIs
- Incluye headers de seguridad y rate limiting

### Ejemplo de Controlador

```php
<?php
namespace App\Controller;

use App\DTO\BookDTO;
use App\Service\BookServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Serializer\Attribute\MapQueryParams;

#[Route('/api/books', name: 'app_book_')]
class BookController extends AbstractController
{
    public function __construct(
        private BookServiceInterface $bookService,
    ) {}

    #[Cache(smaxage: 60)]
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $this->enforceRateLimit($request);
        $books = $this->bookService->findAll();
        return $this->json($books, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $book = $this->bookService->findById($id);
        if (!$book) {
            return $this->json(['error' => 'Libro no encontrado'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($book, Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->enforceRateLimit($request);
        $data = json_decode($request->getContent(), true);
        $dto = BookDTO::fromArray($data);
        
        if ($errors = $dto->validate()) {
            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }
        
        $book = $this->bookService->create($dto);
        return $this->json($book, Response::HTTP_201_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, string $id): JsonResponse
    {
        $this->enforceRateLimit($request);
        $data = json_decode($request->getContent(), true);
        $dto = BookDTO::fromArray($data);
        $book = $this->bookService->update($id, $dto);
        
        if (!$book) {
            return $this->json(['error' => 'Libro no encontrado'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($book, Response::HTTP_OK);
    }

    private function enforceRateLimit(Request $request): void
    {
        $ip = $request->getClientIp();
        $key = $ip;
        $now = time();
        $window = 60;
        $limit = 100;
        
        if (!isset($this->requestLog[$key])) {
            $this->requestLog[$key] = [];
        }
        
        // Limpiar entradas fuera de la ventana
        $this->requestLog[$key] = array_values(array_filter(
            $this->requestLog[$key],
            fn($timestamp) => $timestamp > $now - $window
        ));
        
        if (count($this->requestLog[$key]) >= $limit) {
            throw new \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException(
                $window,
                'Demasiadas solicitudes. Inténtalo nuevamente en un minuto.'
            );
        }
        
        $this->requestLog[$key][] = $now;
    }
}
```

## DTOs (Data Transfer Objects)

### Reglas
- Usar `#[MapQueryParams]` para deserialización automática de query params
- Validación con `validate(): array` que retorna errores
- Inmutables con `fromArray()` factory
- Usar `#[Groups]` de Symfony Serializer para controlar salida JSON

```php
<?php
namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

readonly class BookDTO
{
    public function __construct(
        #[Assert\NotBlank()]
        #[Assert\Length(min: 1, max: 255)]
        public string $title,
        
        #[Assert\NotBlank()]
        #[Assert\Length(max: 255)]
        public string $author,
        
        #[Assert\Length(max: 1000)]
        public string $description = '',
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? '',
            author: $data['author'] ?? '',
            description: $data['description'] ?? '',
        );
    }

    /** @return array<string, string[]> */
    public function validate(): array
    {
        $violations = [];
        $validator = \Symfony\Component\Validator\Validation::createValidator();
        $violationCollection = $validator->validate($this);
        
        foreach ($violationCollection as $violation) {
            $property = $violation->getPropertyPath();
            $violations[$property][] = $violation->getMessage();
        }
        
        return $violations;
    }
}
```

## Entities (Dominio)

### Reglas
- Entidades son **puros** — no contienen lógica de infraestructura
- Usar `#[ORM\Entity]` attributes
- UUID como primary key (ramsey/uuid-doctrine)
- Lifecycle callbacks para timestamps automáticos
- Validación con `#[ORM\...]` y `Symfony\Component\Validator\Constraints`

```php
<?php
namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: BookRepository::class)]
class Book
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[Groups(['book:read'])]
    private string $uuid;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Groups(['book:read'])]
    private string $titleHash;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['book:read'])]
    private string $title;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['book:read'])]
    private string $author;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['book:read'])]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['book:read'])]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['book:read'])]
    private \DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->uuid = $this->generateUuid();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    private function generateUuid(): string
    {
        return strtoupper(bin2hex(random_bytes(16)));
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
        $this->titleHash = md5(strtolower(trim($title)));
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateTimestamps(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
```

## Repository Pattern

### Reglas
- Repositorios extienden `ServiceEntityRepository`
- Métodos custom con nomenclatura `findBy...`, `findOneBy...`, `countBy...`
- Usar Doctrine QueryBuilder para queries complejas
- Nunca exponer EntityManager directamente

```php
<?php
namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function findByTitle(string $title): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.titleHash = :hash')
            ->setParameter('hash', md5(strtolower(trim($title))))
            ->getQuery()
            ->getResult();
    }

    public function countByTitle(string $title): int
    {
        return (int) $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->andWhere('b.titleHash = :hash')
            ->setParameter('hash', md5(strtolower(trim($title))))
            ->getQuery()
            ->getSingleScalarResult();
    }
}
```

## Service Interface (Puerto de Aplicación)

### Reglas
- Definir interfaces de servicio como **puertos**
- Implementar en clases concretas como **adaptadores**
- Inyectar interfaces, nunca implementaciones concretas
- Usar Value Objects para datos complejos

```php
<?php
namespace App\Service;

use App\DTO\BookDTO;
use App\Entity\Book;

interface BookServiceInterface
{
    /** @return Book[] */
    public function findAll(): array;

    public function findById(string $id): ?Book;

    public function findByTitle(string $title): array;

    public function create(BookDTO $dto): Book;

    public function update(string $id, BookDTO $dto): ?Book;

    public function delete(string $id): bool;
}
```

## Testing con PHPUnit 11

### Reglas
- Tests unitarios para Services, DTOs, ValueObjects
- Tests de integración para Controllers
- Mockear dependencias externas
- Usar `setUp()` para inicialización
- Nomenclatura: `test{Method}{Scenario}ExpectedResult`

```php
<?php
namespace App\Tests\Service;

use App\DTO\BookDTO;
use App\Entity\Book;
use App\Repository\BookRepository;
use App\Service\BookService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BookServiceTest extends TestCase
{
    private BookRepository $repository;
    private BookService $service;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(BookRepository::class);
        $this->service = new BookService($this->repository);
    }

    #[Test]
    public function createReturnsPersistedBook(): void
    {
        $dto = new BookDTO(
            title: 'El Principito',
            author: 'Antoine de Saint-Exupéry',
            description: 'Un clásico',
        );

        $book = new Book();
        $book->setTitle($dto->title);
        $book->setAuthor($dto->author);
        $book->setDescription($dto->description);

        $this->repository->method('save')
            ->with($book)
            ->willReturnCallback(fn($b) => $b);

        $result = $this->service->create($dto);

        self::assertInstanceOf(Book::class, $result);
        self::assertSame('El Principito', $result->title);
    }

    #[Test]
    public function findByIdReturnsNullWhenNotFound(): void
    {
        $this->repository->method('find')
            ->with('non-existent-uuid')
            ->willReturn(null);

        $result = $this->service->findById('non-existent-uuid');

        self::assertNull($result);
    }
}
```

## Docker / Symfony Comandos Útiles

```bash
# Instalar dependencias
composer install

# Levantar Docker
docker-compose up -d --build

# Ejecutar tests
docker compose exec app php bin/phpunit

# Crear migración
docker compose exec app php bin/console make:migration

# Aplicar migraciones
docker compose exec app php bin/console doctrine:migrations:migrate

# Clear cache
docker compose exec app php bin/console cache:clear
```

## Checklist de Calidad

- [ ] Controladores delgados (sin lógica de negocio)
- [ ] Interfaces para todos los services
- [ ] DTOs con validación
- [ ] Entity con lifecycle callbacks
- [ ] Repository con queries tipadas
- [ ] Tests unitarios para services
- [ ] Rate limiting en endpoints públicos
- [ ] Security headers
- [ ] HTTP caching cuando aplica
- [ ] Groups de serialización
- [ ] Error handling con códigos HTTP correctos
