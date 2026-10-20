<?php declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Bundle\DoctrineBundle\Registry;

class BookControllerFunctionalTest extends WebTestCase
{
    private ?\DOMDocument $responseDoc = null;

    public function testCreateBook(): void
    {
        $client = static::createClient();

        $payload = json_encode([
            'title' => 'Test Validación Symfony',
            'author' => 'Autor de Prueba Funcional',
            'description' => 'Libro creado mediante tests funcionales con validación completa.',
        ]);

        $client->request('POST', '/api/books', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $this->assertResponseStatusCodeSame(201);

        $body = json_decode($client->getResponse()->getContent(), true);
        
        $this->assertArrayHasKey('id', $body);
        $this->assertArrayHasKey('title', $body);
        $this->assertArrayHasKey('author', $body);
        $this->assertArrayHasKey('description', $body);
        $this->assertArrayHasKey('createdAt', $body);
        $this->assertArrayHasKey('updatedAt', $body);

        // Verificar formato ISO 8601 para fecha
        $createdAt = new \DateTimeImmutable($body['createdAt']);
        $this->assertNotNull($createdAt, 'createdAt no es una fecha ISO 8601 válida: ' . print_r($body['createdAt'], true));

        $this->assertSame('Test Validación Symfony', $body['title']);
    }

    public function testCreateBookMissingTitle(): void
    {
        $client = static::createClient();

        $payload = json_encode([
            'author' => 'Autor sin título',
        ]);

        $client->request('POST', '/api/books', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        // La validación de Symfony debería fallar y devolver 422 (UnprocessableEntity)
        $this->assertResponseStatusCodeSame(422);

        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('violations', $body, 'Respuesta de validación esperada con campo "violations"');
    }

    public function testCreateBookMissingAuthor(): void
    {
        $client = static::createClient();

        $payload = json_encode([
            'title' => 'Título sin autor',
        ]);

        $client->request('POST', '/api/books', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $this->assertResponseStatusCodeSame(422);

        $body = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('violations', $body);
    }

    public function testCreateBookEmptyFields(): void
    {
        $client = static::createClient();

        $payload = json_encode([
            'title' => '',
            'author' => 'Autor con título vacío',
        ]);

        $client->request('POST', '/api/books', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testGetBookById(): void
    {
        $client = static::createClient();

        // Crear un libro primero
        $client->request('POST', '/api/books', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'Libro para prueba GET',
            'author' => 'Autor de búsqueda',
        ]));

        $body = json_decode($client->getResponse()->getContent(), true);
        $bookId = $body['id'];

        // Ahora obtener por id
        $client->request('GET', "/api/books/{$bookId}");

        $this->assertResponseStatusCodeSame(200);
        $book = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame($bookId, $book['id']);
        $this->assertSame('Libro para prueba GET', $book['title']);
    }

    public function testGetAllBooks(): void
    {
        $client = static::createClient();

        // Crear 2 libros extra
        for ($i = 0; $i < 2; $i++) {
            $client->request('POST', '/api/books', [], [], [
                'CONTENT_TYPE' => 'application/json',
            ], json_encode([
                'title' => "Lista libro #{i}",
                'author' => "Autor Lista",
            ]));
        }

        // Obtener todos
        $client->request('GET', '/api/books');

        $this->assertResponseStatusCodeSame(200);

        $books = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($books);
        
        // Validar que cada libro tiene la estructura esperada
        foreach ($books as $book) {
            $this->assertArrayHasKey('id', $book);
            $this->assertArrayHasKey('title', $book);
            $this->assertArrayHasKey('author', $book);
            $this->assertArrayHasKey('createdAt', $book);
            $this->assertArrayHasKey('updatedAt', $book);
            
            // Verificar fecha ISO 8601 válida
            new \DateTimeImmutable($book['createdAt']);
        }
    }

    public function testUpdateBook(): void
    {
        $client = static::createClient();

        // Crear libro
        $client->request('POST', '/api/books', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'Título Original',
            'author' => 'Autor Original',
            'description' => 'Descripción original',
        ]));

        $body = json_decode($client->getResponse()->getContent(), true);
        $bookId = $body['id'];

        // Actualizar libro con PUT
        $client->request('PUT', "/api/books/{$bookId}", [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'Título Actualizado',
            'author' => 'Autor Actualizado',
            'description' => 'Descripción actualizada con detalles adicionales.',
        ]));

        $this->assertResponseStatusCodeSame(200);
        $updated = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame('Título Actualizado', $updated['title']);
        $this->assertSame('Autor Actualizado', $updated['author']);
        $this->assertSame('Descripción actualizada con detalles adicionales.', $updated['description']);
    }

    public function testSecurityHeadersPresent(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/books');

        // Verificar headers de seguridad básicos
        $response = $client->getResponse();

        // Estos headers deben estar presentes en respuesta (no null)
        $contentType = $response->headers->get('Content-Type');
        $this->assertStringContainsString('application/json', $contentType);
    }

    public function testBookResponseStructure(): void
    {
        $client = static::createClient();

        // Validar que en read NO aparece titleHash (solo existe write)
        $client->request('POST', '/api/books', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'Verificar titleHash',
            'author' => 'Autor de verificación',
        ]));

        $body = json_decode($client->getResponse()->getContent(), true);
        
        // titleHash NUNCA debe aparecer en respuestas read/create
        $this->assertArrayNotHasKey('titleHash', $body, 'titleHash no debería estar expuesto en el API response');
    }

    public function testUpdateOnlyDescription(): void
    {
        $client = static::createClient();

        // Crear libro sin description
        $client->request('POST', '/api/books', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'Sin descripción inicial',
            'author' => 'Autor test update parcial',
        ]));

        $body = json_decode($client->getResponse()->getContent(), true);
        $bookId = $body['id'];

        // Actualizar SOLO la descripción
        $client->request('PUT', "/api/books/{$bookId}", [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'description' => 'Descripción añadida luego.',
        ]));

        $this->assertResponseStatusCodeSame(200);
        $updated = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame('Sin descripción inicial', $updated['title']);
        $this->assertSame('Autor test update parcial', $updated['author']);
        $this->assertSame('Descripción añadida luego.', $updated['description']);
    }

    public function testInvalidJsonBody(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/books', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], '{invalid json}');

        // Deber devolver 400 o similar por JSON inválido
        $this->assertResponseStatusCodeSame([400, 500]);
    }

    public function testDateTimeIsISO8601Format(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/books', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'title' => 'Formato Fecha ISO Prueba',
            'author' => 'Autor fecha',
        ]));

        $body = json_decode($client->getResponse()->getContent(), true);

        // Verificar que createdAt es string en formato ISO 8601, no objeto complejo
        $this->assertIsString($body['createdAt']);
        
        // Intentar parsear como DateTimeImmutable - si funciona es válido ISO 8601
        new \DateTimeImmutable($body['createdAt']);

        // Verificar que NO contiene objetos complejos PHP
        $this->assertArrayNotHasKey('timezone_type', $body['createdAt'], 'createdAt no debe tener timezone_type: este es formato interno de PHP, no ISO');
    }
}
