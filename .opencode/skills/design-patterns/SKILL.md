---
name: design-patterns
description: Catálogo de patrones de diseño aplicados a PHP/Symfony. Cubre SOLID, GoF (Creational, Structural, Behavioral), y patrones específicos de Symfony (Service Locator, Event Dispatcher, Strategy, Factory, Repository, etc.). Usa cuando diseñes código, refactorices, o necesites elegir el patrón correcto para un problema.
---

# Patrones de Diseño — PHP & Symfony

## Principios SOLID

### S — Single Responsibility Principle (SRP)
Cada clase tiene una única razón para cambiar.

```php
// ❌ Mal: Clase hace demasiadas cosas
class BookManager
{
    public function save(Book $book) { /* persist */ }
    public function sendNotification(Book $book) { /* email */ }
    public function generatePDF(Book $book) { /* PDF */ }
}

// ✅ Bien: Responsabilidades separadas
class BookService { public function save(Book $book) { /* persist */ } }
class NotificationService { public function send(Book $book) { /* email */ } }
class PDFGenerator { public function generate(Book $book) { /* PDF */ } }
```

### O — Open/Closed Principle (OCP)
Abierto para extensión, cerrado para modificación.

```php
// ✅ Usar interfaces para extender sin modificar
interface DiscountCalculator
{
    public function calculate(float $price): float;
}

class StandardDiscount implements DiscountCalculator
{
    public function calculate(float $price): float { return $price * 0.9; }
}

class VIPDiscount implements DiscountCalculator
{
    public function calculate(float $price): float { return $price * 0.7; }
}

// El código cliente no cambia al agregar nuevos descuentos
class PricingService
{
    public function __construct(private DiscountCalculator $calculator) {}
    public function price(float $base): float { return $this->calculator->calculate($base); }
}
```

### L — Liskov Substitution Principle (LSP)
Subtipos deben ser sustituibles por sus tipos base.

```php
// ✅ Interfaces contractuales respetadas
interface RepositoryInterface
{
    public function find(string $id): ?object;
    public function save(object $entity): void;
}

// Cualquier implementación debe cumplir el contrato
class BookRepository implements RepositoryInterface { /* ... */ }
class UserRepository implements RepositoryInterface { /* ... */ }
```

### I — Interface Segregation Principle (ISP)
Interfaces específicas son mejores que una general.

```php
// ❌ Mal: Interfaz muy grande
interface Worker { public function work(); public function eat(); public function sleep(); }

// ✅ Bien: Interfaces separadas
interface Workable { public function work(); }
interface Feedable { public function eat(); }
interface Restable { public function sleep(); }

class Robot implements Workable { public function work() { /* ... */ } }
// Robot no necesita eat() ni sleep()
```

### D — Dependency Inversion Principle (DIP)
Depender de abstracciones, no de concreciones.

```php
// ❌ Mal: Dependencia concreta
class BookController
{
    private BookRepository $repo; // Acoplado a concreta
    public function __construct() { $this->repo = new BookRepository(); }
}

// ✅ Bien: Dependencia de interfaz
class BookController
{
    public function __construct(
        private BookRepositoryInterface $repo, // Abstracción
    ) {}
}
```

## Patrones GoF — Creacionales

### Factory Pattern
```php
interface EntityFactory
{
    public function create(array $data): Entity;
}

class BookFactory implements EntityFactory
{
    public function create(array $data): Book
    {
        $book = new Book();
        $book->setTitle($data['title']);
        $book->setAuthor($data['author']);
        return $book;
    }
}
```

### Builder Pattern
```php
class BookBuilder
{
    private Book $book;

    public function __construct() { $this->book = new Book(); }

    public function withTitle(string $title): self
    {
        $this->book->setTitle($title);
        return $this;
    }

    public function withAuthor(string $author): self
    {
        $this->book->setAuthor($author);
        return $this;
    }

    public function build(): Book
    {
        return $this->book;
    }
}

// Uso:
$book = (new BookBuilder())
    ->withTitle('El Principito')
    ->withAuthor('Saint-Exupéry')
    ->build();
```

### Singleton (usar con precaución — usar DI container en su lugar)
```php
// En Symfony, preferir servicios singletons del container
// El DI container de Symfony ya es un singleton de facto
```

## Patrones GoF — Estructurales

### Adapter Pattern
```php
// Adaptar una API externa a una interfaz conocida
interface ExternalAPIAdapter
{
    public function fetchBook(string $isbn): ?array;
}

class ISBNdbAdapter implements ExternalAPIAdapter
{
    public function __construct(private HttpClientInterface $client) {}

    public function fetchBook(string $isbn): ?array
    {
        $response = $this->client->get("https://api.isbndb.com/book?isbn={$isbn}");
        return json_decode($response->getBody(), true);
    }
}
```

### Decorator Pattern
```php
interface BookSerializer
{
    public function serialize(Book $book): string;
}

class JSONBookSerializer implements BookSerializer
{
    public function __construct(private SerializerInterface $serializer) {}

    public function serialize(Book $book): string
    {
        return $this->serializer->serialize($book, 'json');
    }
}

// Decorar con logging
class LoggingBookSerializerDecorator implements BookSerializer
{
    public function __construct(
        private BookSerializer $decorated,
        private LoggerInterface $logger,
    ) {}

    public function serialize(Book $book): string
    {
        $this->logger->info('Serializando libro');
        $result = $this->decorated->serialize($book);
        $this->logger->info('Serialización completa');
        return $result;
    }
}
```

### Strategy Pattern
```php
interface SearchStrategy
{
    public function search(string $query): array;
}

class ExactSearch implements SearchStrategy
{
    public function search(string $query): array { /* búsqueda exacta */ }
}

class FuzzySearch implements SearchStrategy
{
    public function search(string $query): array { /* búsqueda difusa */ }
}

class BookSearchService
{
    public function __construct(private SearchStrategy $strategy) {}

    public function execute(string $query): array
    {
        return $this->strategy->search($query);
    }
}

// Cambiar estrategia en runtime
$searchService = new BookSearchService(new FuzzySearch());
$results = $searchService->execute('principito');
```

### Repository Pattern (ya cubierto en hexagonal)
```php
// Ver skill: hexagonal-architecture
```

## Patrones GoF — Conductuales

### Observer / Event Dispatcher (muy usado en Symfony)
```php
// Symfony ya incluye EventDispatcher
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BookService
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
    ) {}

    public function create(Book $book): void
    {
        // ... lógica de creación
        $this->dispatcher->dispatch(new BookCreatedEvent($book));
    }
}

// Listener
class SendWelcomeEmailListener
{
    public function __invoke(BookCreatedEvent $event): void
    {
        // Enviar email al autor
    }
}
```

### Command Pattern
```php
interface Command
{
    public function execute(): mixed;
}

class CreateBookCommand implements Command
{
    public function __construct(
        private BookServiceInterface $service,
        private BookDTO $dto,
    ) {}

    public function execute(): Book
    {
        return $this->service->create($this->dto);
    }
}

// Uso:
$command = new CreateBookCommand($bookService, $dto);
$book = $command->execute();
```

### Template Method Pattern
```php
abstract class BaseExporter
{
    final public function export(array $data): string
    {
        $this->validate($data);
        $result = $this->transform($data);
        return $this->format($result);
    }

    abstract protected function validate(array $data): void;
    abstract protected function transform(array $data): array;
    abstract protected function format(array $data): string;
}

class CSVExporter extends BaseExporter
{
    protected function validate(array $data): void { /* ... */ }
    protected function transform(array $data): array { /* ... */ }
    protected function format(array $data): string { return implode(',', $data); }
}
```

## Patrones Específicos de Symfony

### Service Locator Pattern
```php
// Symfony container es un Service Locator
// Inyección directa es preferida sobre service locator

// ✅ Preferido: DI explícita
class BookController
{
    public function __construct(
        private BookServiceInterface $bookService,
        private SerializerInterface $serializer,
    ) {}
}

// ⚠️ Usar solo cuando no sabes todas las dependencias en compile time
class DynamicServiceLoader
{
    public function __construct(private ServiceLocatorInterface $services) {}

    public function load(string $serviceName): object
    {
        return $this->services->get($serviceName);
    }
}
```

### Listener/Subscriber Pattern
```php
// Kernel Event Listener
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SecurityHeaderListener
{
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $request->headers->set('X-Content-Type-Options', 'nosniff');
        $request->headers->set('X-Frame-Options', 'DENY');
    }
}
```

### Data Transformer (Form/Serializer)
```php
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ISBNToStringTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): string
    {
        if (null === $value) {
            return '';
        }
        return $value->toString();
    }

    public function reverseTransform(mixed $value): ?ISBN
    {
        if (empty($value)) {
            return null;
        }
        try {
            return new ISBN($value);
        } catch (\InvalidArgumentException $e) {
            throw new TransformationFailedException($e->getMessage());
        }
    }
}
```

## Cuándo Usar Cada Patrón

| Problema | Patrón |
|----------|--------|
| Crear objetos complejos | Factory, Builder |
| Compartir recursos | Singleton (con cautela) |
| Extender comportamiento | Decorator |
| Elegir algoritmo en runtime | Strategy |
| Separar abstracción e implementación | Bridge |
| Interfaz incompatible | Adapter |
| Jerarquías de objetos | Composite |
| Múltiples responsabilidades | SRP + Separar clases |
| Eventos async | Observer / Event Dispatcher |
| Operaciones transaccionales | Command |
| Algoritmo con pasos fijos | Template Method |
| Acceso a colección de datos | Repository |

## Checklist de Patrones

- [ ] SRP: Cada clase tiene una responsabilidad clara
- [ ] OCP: Código extensible sin modificar existente
- [ ] LSP: Subtipos sustituibles sin errores
- [ ] ISP: Interfaces pequeñas y específicas
- [ ] DIP: Dependencias de interfaces, no concreciones
- [ ] Patrón elegido resuelve el problema real (no over-engineering)
- [ ] Patrones no añaden complejidad innecesaria
