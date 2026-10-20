<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
final class BookController
{
    private array $requestLog = [];

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    #[Route('/books', name: 'app_books_post', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $this->enforceRateLimit($request);

        $data = json_decode($request->getContent(), true);

        if (!isset($data['title']) || !isset($data['author'])) {
            return new JsonResponse(['error' => 'Title and author are required'], Response::HTTP_BAD_REQUEST);
        }

        $book = new Book();
        $book->setTitle($data['title']);
        $book->setAuthor($data['author']);
        if (isset($data['description'])) {
            $book->setDescription($data['description']);
        }

        $entityManager->persist($book);
        $entityManager->flush();

        return $this->jsonResponse($book->toArray(['book:read']), Response::HTTP_CREATED);
    }

    #[Route('/books/{id}', name: 'app_books_get_one', methods: ['GET'])]
    public function show(Book $book): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $this->enforceRateLimit($request);

            $request->headers->set('X-Content-Type-Options', 'nosniff');
            $request->headers->set('X-Frame-Options', 'DENY');
            $request->headers->set('X-XSS-Protection', '1; mode=block');
            $request->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
            $request->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        }

        return new JsonResponse($book->toArray(['book:read']));
    }

    #[Route('/books', name: 'app_books_get_all', methods: ['GET'])]
    #[Cache(smaxage: 60)]
    public function list(BookRepository $bookRepository): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $this->enforceRateLimit($request);

            $request->headers->set('X-Content-Type-Options', 'nosniff');
            $request->headers->set('X-Frame-Options', 'DENY');
            $request->headers->set('X-XSS-Protection', '1; mode=block');
            $request->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
            $request->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        }

        $books = $bookRepository->findAll();
        $data = [];
        
        foreach ($books as $book) {
            $data[] = $book->toArray(['book:read']);
        }

        return new JsonResponse($data);
    }

    #[Route('/books/{id}', name: 'app_books_put', methods: ['PUT'])]
    public function update(Request $request, EntityManagerInterface $entityManager, Book $book): JsonResponse
    {
        $this->enforceRateLimit($request);

        $request->headers->set('X-Content-Type-Options', 'nosniff');
        $request->headers->set('X-Frame-Options', 'DENY');
        $request->headers->set('X-XSS-Protection', '1; mode=block');
        $request->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $request->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        $data = json_decode($request->getContent(), true);

        if (!empty($data['title'])) {
            $book->setTitle($data['title']);
        }

        if (!empty($data['author'])) {
            $book->setAuthor($data['author']);
        }

        if (array_key_exists('description', $data)) {
            $book->setDescription($data['description']);
        }

        $entityManager->flush();

        return new JsonResponse($book->toArray(['book:read']));
    }

    private function jsonResponse(array $data, int $status = Response::HTTP_OK): JsonResponse
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $request->headers->set('X-Content-Type-Options', 'nosniff');
            $request->headers->set('X-Frame-Options', 'DENY');
            $request->headers->set('X-XSS-Protection', '1; mode=block');
            $request->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
            $request->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        }

        return new JsonResponse($data, $status);
    }

    private function enforceRateLimit(Request $request): void
    {
        $ip = $request->getClientIp();
        $currentTime = microtime(true);
        $window = 60;

        if (!isset($this->requestLog[$ip])) {
            $this->requestLog[$ip] = [];
        }

        $this->requestLog[$ip] = array_values(array_filter(
            $this->requestLog[$ip],
            fn(float $timestamp) => ($currentTime - $timestamp) < $window
        ));

        if (count($this->requestLog[$ip]) >= 100) {
            throw new \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException(
                $window,
                'Demasiadas solicitudes. Inténtalo nuevamente en un minuto.',
            );
        }

        $this->requestLog[$ip][] = $currentTime;
    }
}
