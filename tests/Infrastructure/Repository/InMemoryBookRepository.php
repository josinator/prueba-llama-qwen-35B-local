<?php

declare(strict_types=1);

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

    /** @return Book[] */
    public function searchBooks(string $query, int $offset, int $limit): array
    {
        $query = strtolower($query);
        $results = array_values(array_filter($this->books, function (Book $book) use ($query): bool {
            $title = strtolower((string) $book->getTitle());
            $description = strtolower((string) $book->getDescription());
            return str_contains($title, $query) || str_contains($description, $query);
        }));

        return \array_slice($results, $offset, $limit);
    }

    public function countAll(): int
    {
        return \count($this->books);
    }

    /** @return Book[] */
    public function findAllOrderedByCreatedAt(): array
    {
        $books = array_values($this->books);
        usort($books, function (Book $a, Book $b): int {
            return $a->getCreatedAt()->getTimestamp() <=> $b->getCreatedAt()->getTimestamp();
        });

        return $books;
    }

    /**
     * @return array{
     *     totalBooks: int,
     *     booksWithDescription: int,
     *     booksWithoutDescription: int,
     *     createdToday: int,
     *     updatedToday: int,
     *     authorsCount: array<string, int>,
     *     oldestBookTitle: ?string,
     *     newestBookTitle: ?string
     * }
     */
    public function getStatisticsData(): array
    {
        $books = $this->findAllOrderedByCreatedAt();
        $totalBooks = \count($books);

        $booksWithDescription = 0;
        $booksWithoutDescription = 0;
        $authorsCount = [];
        $createdToday = 0;
        $updatedToday = 0;

        $now = new \DateTimeImmutable();
        $todayStart = $now->setTime(0, 0, 0);

        foreach ($books as $book) {
            $description = $book->getDescription();
            if ($description !== null && $description !== '') {
                $booksWithDescription++;
            } else {
                $booksWithoutDescription++;
            }

            $author = $book->getAuthor();
            $authorsCount[$author] = ($authorsCount[$author] ?? 0) + 1;

            if ($book->getCreatedAt() >= $todayStart) {
                $createdToday++;
            }

            if ($book->getUpdatedAt() >= $todayStart) {
                $updatedToday++;
            }
        }

        arsort($authorsCount);

        $oldestBookTitle = $totalBooks > 0 ? $books[0]->getTitle()->value : null;
        $newestBookTitle = $totalBooks > 0 ? $books[$totalBooks - 1]->getTitle()->value : null;

        return [
            'totalBooks' => $totalBooks,
            'booksWithDescription' => $booksWithDescription,
            'booksWithoutDescription' => $booksWithoutDescription,
            'createdToday' => $createdToday,
            'updatedToday' => $updatedToday,
            'authorsCount' => $authorsCount,
            'oldestBookTitle' => $oldestBookTitle,
            'newestBookTitle' => $newestBookTitle,
        ];
    }

    public function clear(): void
    {
        $this->books = [];
    }
}
