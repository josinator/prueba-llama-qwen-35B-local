<?php

declare(strict_types=1);

namespace App\Tests\Application\DTO;

use App\Application\DTO\CreateBookDTO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CreateBookDTOTest extends TestCase
{
    #[Test]
    public function fromArrayCreatesDto(): void
    {
        $dto = CreateBookDTO::fromArray([
            'title' => 'El Principito',
            'author' => 'Saint-Exupéry',
            'description' => 'A famous book',
        ]);

        self::assertSame('El Principito', $dto->title);
        self::assertSame('Saint-Exupéry', $dto->author);
        self::assertSame('A famous book', $dto->description);
    }

    #[Test]
    public function validateReturnsEmptyArrayOnValidData(): void
    {
        $dto = CreateBookDTO::fromArray([
            'title' => 'Valid Title',
            'author' => 'Valid Author',
        ]);

        self::assertSame([], $dto->validate());
    }

    #[Test]
    public function validateReturnsErrorsOnEmptyTitle(): void
    {
        $dto = CreateBookDTO::fromArray([
            'title' => '',
            'author' => 'Author',
        ]);

        $errors = $dto->validate();
        self::assertArrayHasKey('title', $errors);
    }

    #[Test]
    public function validateReturnsErrorsOnEmptyAuthor(): void
    {
        $dto = CreateBookDTO::fromArray([
            'title' => 'Title',
            'author' => '',
        ]);

        $errors = $dto->validate();
        self::assertArrayHasKey('author', $errors);
    }

    #[Test]
    public function validateReturnsErrorsOnTooLongTitle(): void
    {
        $dto = CreateBookDTO::fromArray([
            'title' => str_repeat('a', 256),
            'author' => 'Author',
        ]);

        $errors = $dto->validate();
        self::assertArrayHasKey('title', $errors);
    }

    #[Test]
    public function validateReturnsErrorsOnTooLongAuthor(): void
    {
        $dto = CreateBookDTO::fromArray([
            'title' => 'Title',
            'author' => str_repeat('a', 201),
        ]);

        $errors = $dto->validate();
        self::assertArrayHasKey('author', $errors);
    }
}
