<?php

declare(strict_types=1);

namespace App\Tests\Application\Service;

use App\Application\BookApplicationService;
use App\Application\BookApplicationServiceInterface;
use App\Application\DTO\CreateBookDTO;
use App\Application\DTO\UpdateBookDTO;
use App\Domain\DomainEvent\BookCreatedEvent;
use App\Domain\DomainEvent\BookUpdatedEvent;
use App\Tests\Infrastructure\Repository\InMemoryBookRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class BookApplicationServiceTest extends TestCase
{
    private BookApplicationServiceInterface $service;

    private InMemoryBookRepository $repository;

    private EventDispatcher $dispatcher;

    protected function setUp(): void
    {
        $this->repository = new InMemoryBookRepository();
        $this->dispatcher = new EventDispatcher();
        $this->service = new BookApplicationService($this->repository, $this->dispatcher);
    }

    #[Test]
    public function createBookCreatesBookSuccessfully(): void
    {
        $dto = CreateBookDTO::fromArray([
            'title' => 'El Principito',
            'author' => 'Saint-Exupéry',
            'description' => 'A famous book',
        ]);

        $book = $this->service->createBook($dto);

        // Book is always created, type is already narrowed by createBook();
        self::assertSame('El Principito', $book->getTitle()->value);
        self::assertSame('Saint-Exupéry', $book->getAuthor());
        self::assertSame('A famous book', $book->getDescription());
    }

    #[Test]
    public function createBookThrowsOnInvalidDto(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $dto = CreateBookDTO::fromArray([
            'title' => '',
            'author' => '',
        ]);

        $this->service->createBook($dto);
    }

    #[Test]
    public function createBookThrowsOnDuplicateTitle(): void
    {
        $dto1 = CreateBookDTO::fromArray([
            'title' => 'El Principito',
            'author' => 'Author 1',
        ]);

        $this->service->createBook($dto1);

        $this->expectException(\DomainException::class);

        $dto2 = CreateBookDTO::fromArray([
            'title' => 'El Principito',
            'author' => 'Author 2',
        ]);

        $this->service->createBook($dto2);
    }

    #[Test]
    public function createBookDispatchesEvent(): void
    {
        $eventReceived = null;
        $this->dispatcher->addListener(BookCreatedEvent::class, function ($event) use (&$eventReceived) {
            $eventReceived = $event;
        });

        $dto = CreateBookDTO::fromArray([
            'title' => 'El Principito',
            'author' => 'Saint-Exupéry',
        ]);

        $book = $this->service->createBook($dto);

        // Event is dispatched
        self::assertSame($book->getId(), $eventReceived->bookId);
        self::assertSame('El Principito', $eventReceived->title);
    }

    #[Test]
    public function getBookReturnsBook(): void
    {
        $dto = CreateBookDTO::fromArray([
            'title' => 'El Principito',
            'author' => 'Saint-Exupéry',
        ]);

        $book = $this->service->createBook($dto);
        $found = $this->service->getBook($book->getId());

        // Found is type-narrowed by getBook();
        self::assertSame($book->getId(), $found->getId());
    }

    #[Test]
    public function getBookReturnsNullWhenNotFound(): void
    {
        $result = $this->service->getBook('non-existent-id');
        self::assertNull($result);
    }

    #[Test]
    public function listBooksReturnsArray(): void
    {
        $dto1 = CreateBookDTO::fromArray([
            'title' => 'Book 1',
            'author' => 'Author 1',
        ]);

        $dto2 = CreateBookDTO::fromArray([
            'title' => 'Book 2',
            'author' => 'Author 2',
        ]);

        $this->service->createBook($dto1);
        $this->service->createBook($dto2);

        $books = $this->service->listBooks();

        self::assertCount(2, $books);
    }

    #[Test]
    public function updateBookUpdatesSuccessfully(): void
    {
        $createDto = CreateBookDTO::fromArray([
            'title' => 'Original Title',
            'author' => 'Original Author',
        ]);

        $book = $this->service->createBook($createDto);

        $updateDto = UpdateBookDTO::fromArray([
            'title' => 'Updated Title',
        ]);

        $updated = $this->service->updateBook($book->getId(), $updateDto);

        // Updated is type-narrowed by updateBook();
        self::assertSame('Updated Title', $updated->getTitle()->value);
        self::assertSame('Original Author', $updated->getAuthor());
    }

    #[Test]
    public function updateBookReturnsNullWhenNotFound(): void
    {
        $updateDto = UpdateBookDTO::fromArray([
            'title' => 'New Title',
        ]);

        $result = $this->service->updateBook('non-existent-id', $updateDto);
        self::assertNull($result);
    }

    #[Test]
    public function updateBookDispatchesEvent(): void
    {
        $createDto = CreateBookDTO::fromArray([
            'title' => 'Original Title',
            'author' => 'Author',
        ]);

        $book = $this->service->createBook($createDto);

        $eventReceived = null;
        $this->dispatcher->addListener(BookUpdatedEvent::class, function ($event) use (&$eventReceived) {
            $eventReceived = $event;
        });

        $updateDto = UpdateBookDTO::fromArray([
            'title' => 'Updated Title',
        ]);

        $this->service->updateBook($book->getId(), $updateDto);

        // Event is dispatched
        self::assertSame($book->getId(), $eventReceived->bookId);
        self::assertSame('Updated Title', $eventReceived->title);
    }

    #[Test]
    public function deleteBookRemovesSuccessfully(): void
    {
        $dto = CreateBookDTO::fromArray([
            'title' => 'To Delete',
            'author' => 'Author',
        ]);

        $book = $this->service->createBook($dto);

        $result = $this->service->deleteBook($book->getId());

        self::assertTrue($result);
        self::assertNull($this->service->getBook($book->getId()));
    }

    #[Test]
    public function deleteBookReturnsFalseWhenNotFound(): void
    {
        $result = $this->service->deleteBook('non-existent-id');
        self::assertFalse($result);
    }
}
