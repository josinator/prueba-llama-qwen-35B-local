<?php

declare(strict_types=1);

namespace App\Application\Port;

use App\Application\DTO\SearchBooksDTO;
use App\Domain\Entity\Book;

interface BookSearchServiceInterface
{
    /**
     * @return array{
     *     books: Book[],
     *     total: int,
     *     page: int,
     *     limit: int,
     *     totalPages: int
     * }
     */
    public function searchBooks(SearchBooksDTO $dto): array;
}
