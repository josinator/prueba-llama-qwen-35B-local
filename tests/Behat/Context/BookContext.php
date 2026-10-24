<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use Behat\Gherkin\Node\PyStringNode;

class BookContext extends ApiContext
{
    private ?string $lastCreatedBookId = null;

    public function iCreateABookWithFields(PyStringNode $json): void
    {
        $this->sendRequest('POST', '/api/books', $json->getRaw());
    }

    public function iUpdateBookWithFields(string $id, PyStringNode $json): void
    {
        $this->sendRequest('PUT', "/api/books/{$id}", $json->getRaw());
    }

    public function iDeleteBook(string $id): void
    {
        $this->sendRequest('DELETE', "/api/books/{$id}");
    }

    public function iGetBook(string $id): void
    {
        $this->sendRequest('GET', "/api/books/{$id}");
    }

    public function iSearchBooksWithQuery(string $query): void
    {
        $this->sendRequest('GET', "/dashboard/api/search?q={$query}&page=1&limit=20");
    }

    public function iRequestStats(): void
    {
        $this->sendRequest('GET', '/dashboard/api/stats');
    }

    public function storeCreatedBookId(): void
    {
        if (isset($this->response['id'])) {
            $this->lastCreatedBookId = $this->response['id'];
        }
    }

    public function assertBookStructure(): void
    {
        $this->assertResponseHasKey('id');
        $this->assertResponseHasKey('title');
        $this->assertResponseHasKey('author');
        $this->assertResponseHasKey('description');
        $this->assertResponseHasKey('createdAt');
        $this->assertResponseHasKey('updatedAt');
    }

    public function getLastCreatedBookId(): ?string
    {
        return $this->lastCreatedBookId;
    }
}
