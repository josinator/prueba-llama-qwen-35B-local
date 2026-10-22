<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\BookApplicationServiceInterface;
use App\Application\BookStatisticsServiceInterface;
use App\Application\DTO\CreateBookDTO;
use App\Application\DTO\SearchBooksDTO;
use App\Application\DTO\UpdateBookDTO;
use App\Application\Port\BookChangeTrackerInterface;
use App\Application\Port\BookSearchServiceInterface;
use App\Domain\Entity\Book;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dashboard', name: 'app_dashboard_')]
final class DashboardController extends AbstractController
{
    public function __construct(
        private BookApplicationServiceInterface $bookService,
        private BookStatisticsServiceInterface $statisticsService,
        private BookSearchServiceInterface $bookSearchService,
        private BookChangeTrackerInterface $changeTracker,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('dashboard/index.html.twig');
    }

    #[Route('/api/stats', name: 'api_stats', methods: ['GET'])]
    public function stats(): JsonResponse
    {
        $statistics = $this->statisticsService->getStatistics();
        return $this->json([
            'totalBooks' => $statistics->totalBooks,
            'booksWithDescription' => $statistics->booksWithDescription,
            'booksWithoutDescription' => $statistics->booksWithoutDescription,
            'createdToday' => $statistics->createdToday,
            'updatedToday' => $statistics->updatedToday,
            'authorsCount' => $statistics->authorsCount,
            'oldestBookTitle' => $statistics->oldestBookTitle,
            'newestBookTitle' => $statistics->newestBookTitle,
        ]);
    }

    #[Route('/api/search', name: 'api_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = trim($request->query->get('q', ''));
        $page = (int) $request->query->get('page', 1);
        $limit = (int) $request->query->get('limit', 20);

        $dto = SearchBooksDTO::fromRequest($query, $page, $limit);
        $result = $this->bookSearchService->searchBooks($dto);

        $books = array_map(fn (Book $b) => $this->serializeBook($b), $result['books']);

        return $this->json([
            'books' => $books,
            'total' => $result['total'],
            'page' => $result['page'],
            'limit' => $result['limit'],
            'totalPages' => $result['totalPages'],
        ]);
    }

    #[Route('/api/books', name: 'api_create', methods: ['POST'])]
    public function createBook(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return $this->json(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }

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

    #[Route('/api/books/{id}', name: 'api_update', methods: ['PUT'])]
    public function updateBook(Request $request, string $id): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return $this->json(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }

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

    #[Route('/api/books/{id}', name: 'api_delete', methods: ['DELETE'])]
    public function deleteBook(string $id): JsonResponse
    {
        $book = $this->bookService->getBook($id);
        if (!$book) {
            return $this->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        $this->bookService->deleteBook($id);

        return $this->json(['success' => true]);
    }

    #[Route('/api/books/{id}', name: 'api_show', methods: ['GET'])]
    public function showBook(string $id): JsonResponse
    {
        $book = $this->bookService->getBook($id);
        if (!$book) {
            return $this->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }
        return $this->json($this->serializeBook($book));
    }

    #[Route('/sse', name: 'api_sse', methods: ['GET'])]
    public function sse(): StreamedResponse
    {
        $response = new StreamedResponse();
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        $lastEventId = (int) $response->headers->get('Last-Event-ID', '0');

        $callback = function (array $event) use (&$lastEventId): void {
            $lastEventId++;
            $data = json_encode($event['data'], JSON_UNESCAPED_UNICODE);
            $output = "id: {$lastEventId}\nevent: {$event['type']}\ndata: {$data}\n\n";
            echo $output;
            ob_flush();
            flush();
        };

        $this->changeTracker->subscribe($callback);

        $response->setCallback(function (): void {
            $interval = 2;
            while (true) {
                if (connection_aborted()) {
                    break;
                }
                usleep($interval * 1000000);
            }
        });

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeBook(Book $book): array
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
