<?php

declare(strict_types=1);

namespace App\Application\DTO;

readonly class CreateBookDTO
{
    public function __construct(
        public string $title,
        public string $author,
        public ?string $description = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? '',
            author: $data['author'] ?? '',
            description: $data['description'] ?? null,
        );
    }

    /** @return array<string, list<string>> */
    public function validate(): array
    {
        $errors = [];
        if (empty(trim($this->title))) {
            $errors['title'][] = 'Title is required';
        }
        if (strlen($this->title) > 255) {
            $errors['title'][] = 'Title cannot exceed 255 characters';
        }
        if (empty(trim($this->author))) {
            $errors['author'][] = 'Author is required';
        }
        if (strlen($this->author) > 200) {
            $errors['author'][] = 'Author cannot exceed 200 characters';
        }
        return $errors;
    }
}
