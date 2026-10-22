<?php

declare(strict_types=1);

namespace App\Application;

use App\Application\DTO\BookStatisticsDTO;
use App\Application\Port\BookRepositoryInterface;

final class BookStatisticsService implements BookStatisticsServiceInterface
{
    public function __construct(
        private BookRepositoryInterface $bookRepository,
    ) {
    }

    public function getStatistics(): BookStatisticsDTO
    {
        $data = $this->bookRepository->getStatisticsData();

        return new BookStatisticsDTO(
            totalBooks: $data['totalBooks'],
            booksWithDescription: $data['booksWithDescription'],
            booksWithoutDescription: $data['booksWithoutDescription'],
            createdToday: $data['createdToday'],
            updatedToday: $data['updatedToday'],
            authorsCount: $data['authorsCount'],
            oldestBookTitle: $data['oldestBookTitle'],
            newestBookTitle: $data['newestBookTitle'],
        );
    }
}
