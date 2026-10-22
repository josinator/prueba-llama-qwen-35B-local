<?php

declare(strict_types=1);

namespace App\Infrastructure\EventListener;

use App\Application\Port\BookChangeTrackerInterface;
use App\Domain\DomainEvent\BookCreatedEvent;
use App\Domain\DomainEvent\BookUpdatedEvent;
use Psr\Log\LoggerInterface;

final class DomainEventListener
{
    public function __construct(
        private LoggerInterface $logger,
        private BookChangeTrackerInterface $changeTracker,
    ) {
    }

    public function onBookCreated(BookCreatedEvent $event): void
    {
        $this->logger->info('Book created', [
            'bookId' => $event->bookId,
            'title' => $event->title,
        ]);

        $this->changeTracker->notifyBookCreated(
            $event->bookId,
            $event->title,
            $event->createdAt,
        );
    }

    public function onBookUpdated(BookUpdatedEvent $event): void
    {
        $this->logger->info('Book updated', [
            'bookId' => $event->bookId,
            'title' => $event->title,
        ]);

        $this->changeTracker->notifyBookUpdated(
            $event->bookId,
            $event->title,
            $event->updatedAt,
        );
    }
}
