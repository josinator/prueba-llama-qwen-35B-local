<?php

declare(strict_types=1);

namespace App\Application\Port;

interface BookChangeTrackerInterface
{
    public function subscribe(callable $callback): void;

    public function notifyBookCreated(string $bookId, string $title, \DateTimeImmutable $createdAt): void;

    public function notifyBookUpdated(string $bookId, string $title, \DateTimeImmutable $updatedAt): void;

    public function notifyBookDeleted(string $bookId, string $title): void;
}
