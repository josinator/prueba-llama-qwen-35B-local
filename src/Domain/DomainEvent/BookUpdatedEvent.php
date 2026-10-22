<?php

declare(strict_types=1);

namespace App\Domain\DomainEvent;

final class BookUpdatedEvent
{
    public function __construct(
        public readonly string $bookId,
        public readonly string $title,
        public readonly \DateTimeImmutable $updatedAt,
    ) {
    }
}
