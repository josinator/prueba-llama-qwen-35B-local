<?php

declare(strict_types=1);

namespace App\Application;

use App\Application\DTO\SearchBooksDTO;
use App\Application\Port\BookRepositoryInterface;
use App\Application\Port\BookSearchServiceInterface;

final class BookSearchService implements BookSearchServiceInterface
{
    public function __construct(
        private BookRepositoryInterface $bookRepository,
    ) {
    }

    public function searchBooks(SearchBooksDTO $dto): array
    {
        if ($dto->query === '') {
            $total = $this->bookRepository->countAll();
            $allBooks = $this->bookRepository->findAll();
            $paginated = \array_slice($allBooks, $dto->getOffset(), $dto->limit);
        } else {
            $paginated = $this->bookRepository->searchBooks($dto->query, $dto->getOffset(), $dto->limit);
            $total = \count($paginated);
        }

        $totalPages = $dto->query === '' ? max(1, (int) \ceil($total / $dto->limit)) : 1;

        return [
            'books' => $paginated,
            'total' => $total,
            'page' => $dto->page,
            'limit' => $dto->limit,
            'totalPages' => $totalPages,
        ];
    }
}
