<?php

declare(strict_types=1);

namespace App\Application\DTO;

final readonly class SearchBooksDTO
{
    public function __construct(
        public string $query = '',
        public int $page = 1,
        public int $limit = 20,
    ) {
    }

    public static function fromRequest(string $query = '', int $page = 1, int $limit = 20): self
    {
        return new self(
            query: $query,
            page: $page < 1 ? 1 : $page,
            limit: $limit < 1 || $limit > 100 ? 20 : $limit,
        );
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->limit;
    }
}
