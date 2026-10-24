<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use App\Tests\Behat\KernelBootTrait;
use PHPUnit\Framework\Assert;

class ApiContext extends Assert
{
    use KernelBootTrait;

    /** @var array<string, mixed> */
    private array $response = [];

    private int $statusCode = 0;

    private string $baseUrl = 'http://localhost:8080';

    public function setBaseUrl(string $url): void
    {
        $this->baseUrl = $url;
    }

    public function sendRequest(string $method, string $path, ?string $body = null): void
    {
        $client = $this->bootClient();
        $requestHeaders = ['CONTENT_TYPE' => 'application/json'];

        $client->request($method, $this->baseUrl . $path, [], [], $requestHeaders, $body ?? '');

        $this->statusCode = $client->getResponse()->getStatusCode();
        $content = $client->getResponse()->getContent() ?: '';

        try {
            $this->response = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->response = ['raw' => $content];
        }
    }

    public function assertStatusCode(int $expected): void
    {
        $this->assertEquals($expected, $this->statusCode);
    }

    public function assertResponseHasKey(string $key): void
    {
        $this->assertArrayHasKey($key, $this->response);
    }

    public function assertResponseHasKeyWithValue(string $key, mixed $value): void
    {
        $this->assertArrayHasKey($key, $this->response);
        $this->assertEquals($value, $this->response[$key]);
    }

    public function assertResponseIsArray(): void
    {
        // Response is always array (typed as array<string, mixed>)
    }

    public function assertResponseContains(string $needle): void
    {
        $content = json_encode($this->response) ?: '[]';
        $this->assertStringContainsString($needle, $content);
    }

    /**
     * @param array<string, string> $structure
     */
    public function assertJsonResponseStructure(array $structure): void
    {
        foreach ($structure as $key => $type) {
            $this->assertArrayHasKey($key, $this->response);
            if ($type === 'array') {
                $this->assertIsArray($this->response[$key]);
            } elseif ($type === 'int') {
                $this->assertIsInt($this->response[$key]);
            } elseif ($type === 'string') {
                $this->assertIsString($this->response[$key]);
            } elseif ($type === 'bool') {
                $this->assertIsBool($this->response[$key]);
            }
        }
    }

    public function getResponseValue(string $key): mixed
    {
        return $this->response[$key] ?? null;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
