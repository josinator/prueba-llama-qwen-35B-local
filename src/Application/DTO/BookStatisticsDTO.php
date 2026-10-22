<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class BookStatisticsDTO
{
    public function __construct(
        public int $totalBooks,
        public int $booksWithDescription,
        public int $booksWithoutDescription,
        public int $createdToday,
        public int $updatedToday,
        /** @var array<string, int> */
        public array $authorsCount,
        public ?string $oldestBookTitle,
        public ?string $newestBookTitle,
    ) {
    }
}
