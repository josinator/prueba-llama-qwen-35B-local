<?php declare(strict_types=1);

namespace App\Infrastructure\EventListener;

use App\Domain\DomainEvent\BookCreatedEvent;
use App\Domain\DomainEvent\BookUpdatedEvent;
use Psr\Log\LoggerInterface;

final class DomainEventListener
{
    public function __construct(
        private LoggerInterface $logger,
    ) {}

    public function onBookCreated(BookCreatedEvent $event): void
    {
        $this->logger->info('Book created', [
            'bookId' => $event->bookId,
            'title' => $event->title,
        ]);
    }

    public function onBookUpdated(BookUpdatedEvent $event): void
    {
        $this->logger->info('Book updated', [
            'bookId' => $event->bookId,
            'title' => $event->title,
        ]);
    }
}
