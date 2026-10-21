<?php declare(strict_types=1);

namespace App\Application\DTO;

readonly class UpdateBookDTO
{
    public function __construct(
        public ?string $title = null,
        public ?string $author = null,
        public ?string $description = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            author: $data['author'] ?? null,
            description: $data['description'] ?? null,
        );
    }

    public function hasChanges(): bool
    {
        return $this->title !== null || $this->author !== null || $this->description !== null;
    }

    /** @return array<string, string[]> */
    public function validate(): array
    {
        $errors = [];
        if ($this->title !== null && strlen($this->title) > 255) {
            $errors['title'][] = 'Title cannot exceed 255 characters';
        }
        if ($this->author !== null && strlen($this->author) > 200) {
            $errors['author'][] = 'Author cannot exceed 200 characters';
        }
        return $errors;
    }
}
