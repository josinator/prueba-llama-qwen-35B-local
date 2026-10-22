<?php

declare(strict_types=1);

namespace App\Application;

use App\Application\DTO\CreateBookDTO;
use App\Application\DTO\UpdateBookDTO;
use App\Domain\Entity\Book;

interface BookApplicationServiceInterface
{
    /** @return Book[] */
    public function listBooks(): array;

    public function getBook(string $id): ?Book;

    public function createBook(CreateBookDTO $dto): Book;

    public function updateBook(string $id, UpdateBookDTO $dto): ?Book;

    public function deleteBook(string $id): bool;
}
