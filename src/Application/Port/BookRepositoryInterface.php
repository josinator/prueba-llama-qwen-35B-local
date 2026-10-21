<?php declare(strict_types=1);

namespace App\Application\Port;

use App\Domain\Entity\Book;

interface BookRepositoryInterface
{
    public function save(Book $book): void;

    public function delete(Book $book): void;

    public function find(string $id): ?Book;

    /** @return Book[] */
    public function findAll(): array;

    /** @return Book[] */
    public function findByTitleHash(string $titleHash): array;
}
