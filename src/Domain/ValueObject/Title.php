<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

readonly class Title
{
    public function __construct(
        public string $value
    ) {
        if (empty(trim($value))) {
            throw new \InvalidArgumentException('Title cannot be empty');
        }
        if (strlen($value) > 255) {
            throw new \InvalidArgumentException('Title cannot exceed 255 characters');
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function hash(): string
    {
        return strtolower(md5(trim($this->value)));
    }
}
