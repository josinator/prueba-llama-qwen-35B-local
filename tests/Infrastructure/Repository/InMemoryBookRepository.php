<?php declare(strict_types=1);

namespace App\Tests\Infrastructure\Repository;

use App\Application\Port\BookRepositoryInterface;
use App\Domain\Entity\Book;

final class InMemoryBookRepository implements BookRepositoryInterface
{
    /** @var array<string, Book> */
    private array $books = [];

    public function save(Book $book): void
    {
        $this->books[$book->getId()] = $book;
    }

    public function delete(Book $book): void
    {
        unset($this->books[$book->getId()]);
    }

    public function find(string $id): ?Book
    {
        return $this->books[$id] ?? null;
    }

    /** @return Book[] */
    public function findAll(): array
    {
        return $this->books;
    }

    /** @return Book[] */
    public function findByTitleHash(string $titleHash): array
    {
        return array_values(array_filter($this->books, function (Book $book) use ($titleHash) {
            return strtolower(md5(trim((string) $book->getTitle()))) === $titleHash;
        }));
    }

    public function clear(): void
    {
        $this->books = [];
    }
}
