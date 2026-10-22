<?php

declare(strict_types=1);

namespace App\Tests\Domain\ValueObject;

use App\Domain\ValueObject\Title;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TitleTest extends TestCase
{
    #[Test]
    public function titleCreatesWithValue(): void
    {
        $title = new Title('El Principito');
        self::assertSame('El Principito', $title->value);
    }

    #[Test]
    public function titleThrowsOnEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Title('');
    }

    #[Test]
    public function titleThrowsOnWhitespaceOnly(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Title('   ');
    }

    #[Test]
    public function titleThrowsOnTooLongString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Title(str_repeat('a', 256));
    }

    #[Test]
    public function titleHashReturnsMd5(): void
    {
        $title = new Title('El Principito');
        self::assertSame(strtolower(md5('El Principito')), $title->hash());
    }

    #[Test]
    public function titleHashIsConsistent(): void
    {
        $title1 = new Title('Test Book');
        $title2 = new Title('Test Book');
        self::assertSame($title1->hash(), $title2->hash());
    }

    #[Test]
    public function titleToStringReturnsValue(): void
    {
        $title = new Title('Test');
        self::assertSame('Test', (string) $title);
    }
}
