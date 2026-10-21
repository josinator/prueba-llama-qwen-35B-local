<?php declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\Title;

final class Book
{
    private string $id;
    private Title $title;
    private string $author;
    private ?string $description;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        ?string $id = null,
        ?Title $title = null,
        ?string $author = null,
        ?string $description = null,
        ?\DateTimeImmutable $createdAt = null,
    ) {
        $this->id = $id ?? $this->generateId();
        $this->title = $title ?? new Title('');
        $this->author = $author ?? '';
        $this->description = $description;
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): Title
    {
        return $this->title;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setTitle(Title $title): void
    {
        $this->title = $title;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function setAuthor(string $author): void
    {
        $this->author = $author;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
        $this->updatedAt = new \DateTimeImmutable();
    }

    private function generateId(): string
    {
        return strtoupper(bin2hex(random_bytes(16)));
    }
}
