<?php

declare(strict_types=1);

namespace App\Application;

use App\Application\Port\BookChangeTrackerInterface;

final class BookChangeTrackerService implements BookChangeTrackerInterface
{
    /**
     * @var list<callable>
     */
    private array $subscribers = [];

    public function subscribe(callable $callback): void
    {
        $this->subscribers[] = $callback;
    }

    public function notifyBookCreated(string $bookId, string $title, \DateTimeImmutable $createdAt): void
    {
        $this->notify('book.created', [
            'bookId' => $bookId,
            'title' => $title,
            'createdAt' => $createdAt->format('Y-m-d\TH:i:sP'),
        ]);
    }

    public function notifyBookUpdated(string $bookId, string $title, \DateTimeImmutable $updatedAt): void
    {
        $this->notify('book.updated', [
            'bookId' => $bookId,
            'title' => $title,
            'updatedAt' => $updatedAt->format('Y-m-d\TH:i:sP'),
        ]);
    }

    public function notifyBookDeleted(string $bookId, string $title): void
    {
        $this->notify('book.deleted', [
            'bookId' => $bookId,
            'title' => $title,
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function notify(string $type, array $data): void
    {
        $event = [
            'type' => $type,
            'data' => $data,
            'timestamp' => time(),
        ];

        foreach ($this->subscribers as $callback) {
            $callback($event);
        }
    }
}
