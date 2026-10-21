<?php declare(strict_types=1);

namespace App\Application;

use App\Application\DTO\CreateBookDTO;
use App\Application\DTO\UpdateBookDTO;
use App\Application\Port\BookRepositoryInterface;
use App\Domain\DomainEvent\BookCreatedEvent;
use App\Domain\DomainEvent\BookUpdatedEvent;
use App\Domain\Entity\Book;
use App\Domain\ValueObject\Title;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class BookApplicationService implements BookApplicationServiceInterface
{
    public function __construct(
        private BookRepositoryInterface $repository,
        private EventDispatcherInterface $dispatcher,
    ) {}

    public function listBooks(): array
    {
        return $this->repository->findAll();
    }

    public function getBook(string $id): ?Book
    {
        return $this->repository->find($id);
    }

    public function createBook(CreateBookDTO $dto): Book
    {
        $errors = $dto->validate();
        if (!empty($errors)) {
            throw new \InvalidArgumentException('Validation failed');
        }

        $existing = $this->repository->findByTitleHash((new Title($dto->title))->hash());
        if (!empty($existing)) {
            throw new \DomainException('A book with this title already exists');
        }

        $book = new Book(
            title: new Title($dto->title),
            author: $dto->author,
            description: $dto->description,
        );

        $this->repository->save($book);

        $this->dispatcher->dispatch(new BookCreatedEvent(
            $book->getId(),
            $book->getTitle()->value,
            $book->getCreatedAt(),
        ));

        return $book;
    }

    public function updateBook(string $id, UpdateBookDTO $dto): ?Book
    {
        if (!$dto->hasChanges()) {
            throw new \InvalidArgumentException('No changes provided');
        }

        $errors = $dto->validate();
        if (!empty($errors)) {
            throw new \InvalidArgumentException('Validation failed');
        }

        $book = $this->repository->find($id);
        if (!$book) {
            return null;
        }

        if ($dto->title !== null) {
            $book->setTitle(new Title($dto->title));
        }
        if ($dto->author !== null) {
            $book->setAuthor($dto->author);
        }
        if ($dto->description !== null) {
            $book->setDescription($dto->description);
        }

        $this->repository->save($book);

        $this->dispatcher->dispatch(new BookUpdatedEvent(
            $book->getId(),
            $book->getTitle()->value,
            $book->getUpdatedAt(),
        ));

        return $book;
    }

    public function deleteBook(string $id): bool
    {
        $book = $this->repository->find($id);
        if (!$book) {
            return false;
        }

        $this->repository->delete($book);
        return true;
    }
}
