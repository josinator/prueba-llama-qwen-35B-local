<?php declare(strict_types=1);

namespace App\Controller;

use App\Application\BookApplicationServiceInterface;
use App\Application\DTO\CreateBookDTO;
use App\Application\DTO\UpdateBookDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/books', name: 'app_book_')]
final class BookController extends AbstractController
{
    public function __construct(
        private BookApplicationServiceInterface $bookService,
    ) {}

    #[Cache(smaxage: 60)]
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $books = $this->bookService->listBooks();
        return $this->json(array_map(fn($b) => $this->serializeBook($b), $books));
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $book = $this->bookService->getBook($id);
        if (!$book) {
            return $this->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($this->serializeBook($book));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }
        
        $dto = CreateBookDTO::fromArray($data);

        $validationErrors = $dto->validate();
        if ($validationErrors) {
            $violations = [];
            foreach ($validationErrors as $field => $messages) {
                $violations[] = [
                    'field' => $field,
                    'message' => $messages[0],
                ];
            }
            return $this->json(['violations' => $violations], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $book = $this->bookService->createBook($dto);
            return $this->json($this->serializeBook($book), Response::HTTP_CREATED);
        } catch (\DomainException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Request $request, string $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!is_array($data)) {
            return $this->json(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }
        
        $dto = UpdateBookDTO::fromArray($data);

        $validationErrors = $dto->validate();
        if ($validationErrors) {
            $violations = [];
            foreach ($validationErrors as $field => $messages) {
                $violations[] = [
                    'field' => $field,
                    'message' => $messages[0],
                ];
            }
            return $this->json(['violations' => $violations], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $book = $this->bookService->updateBook($id, $dto);
            if (!$book) {
                return $this->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
            }
            return $this->json($this->serializeBook($book));
        } catch (\DomainException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        }
    }

    private function serializeBook(object $book): array
    {
        return [
            'id' => $book->getId(),
            'title' => $book->getTitle()->value,
            'author' => $book->getAuthor(),
            'description' => $book->getDescription(),
            'createdAt' => $book->getCreatedAt()->format('Y-m-d\TH:i:sP'),
            'updatedAt' => $book->getUpdatedAt()->format('Y-m-d\TH:i:sP'),
        ];
    }
}
