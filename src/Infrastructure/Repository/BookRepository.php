<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Application\Port\BookRepositoryInterface;
use App\Domain\Entity\Book as DomainBook;
use App\Domain\ValueObject\Title;
use App\Infrastructure\Entity\Book as DoctrineBook;
use Doctrine\ORM\EntityManagerInterface;

class BookRepository implements BookRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function save(DomainBook $book): void
    {
        $doctrineBook = $this->toDoctrine($book);
        $this->em->persist($doctrineBook);
        $this->em->flush();
    }

    public function delete(DomainBook $book): void
    {
        $doctrineBook = $this->toDoctrine($book);
        $this->em->remove($doctrineBook);
        $this->em->flush();
    }

    public function find(mixed $id): ?DomainBook
    {
        if (!is_string($id)) {
            return null;
        }
        $doctrineBook = $this->em->find(DoctrineBook::class, $id);
        return $doctrineBook ? $this->toDomain($doctrineBook) : null;
    }

    /** @return DomainBook[] */
    public function findAll(): array
    {
        $doctrineBooks = $this->em->getRepository(DoctrineBook::class)->findAll();
        return array_map(fn (DoctrineBook $db) => $this->toDomain($db), $doctrineBooks);
    }

    /** @return DomainBook[] */
    public function findByTitleHash(string $titleHash): array
    {
        $doctrineBooks = $this->em->getRepository(DoctrineBook::class)
            ->findBy(['titleHash' => $titleHash], ['createdAt' => 'ASC']);

        return array_map(fn (DoctrineBook $db) => $this->toDomain($db), $doctrineBooks);
    }

    /** @return DomainBook[] */
    public function searchBooks(string $query, int $offset, int $limit): array
    {
        $doctrineBooks = $this->em->getRepository(DoctrineBook::class)
            ->createQueryBuilder('b')
            ->where('LOWER(b.title) LIKE :query')
            ->orWhere('LOWER(b.description) LIKE :query')
            ->setParameter('query', '%' . strtolower($query) . '%')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->orderBy('b.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();

        return array_map(fn (DoctrineBook $db) => $this->toDomain($db), $doctrineBooks);
    }

    public function countAll(): int
    {
        return $this->em->getRepository(DoctrineBook::class)
            ->count([]);
    }

    /** @return DomainBook[] */
    public function findAllOrderedByCreatedAt(): array
    {
        $doctrineBooks = $this->em->getRepository(DoctrineBook::class)
            ->findAll();

        usort($doctrineBooks, function (DoctrineBook $a, DoctrineBook $b): int {
            return $a->getCreatedAt()->getTimestamp() <=> $b->getCreatedAt()->getTimestamp();
        });

        return array_map(fn (DoctrineBook $db) => $this->toDomain($db), $doctrineBooks);
    }

    /**
     * @return array{
     *     totalBooks: int,
     *     booksWithDescription: int,
     *     booksWithoutDescription: int,
     *     createdToday: int,
     *     updatedToday: int,
     *     authorsCount: array<string, int>,
     *     oldestBookTitle: ?string,
     *     newestBookTitle: ?string
     * }
     */
    public function getStatisticsData(): array
    {
        $repo = $this->em->getRepository(DoctrineBook::class);
        $now = new \DateTimeImmutable();
        $todayStart = $now->setTime(0, 0, 0);

        $totalBooks = $repo->count([]);

        $booksWithDescription = (int) $this->em->createQueryBuilder()
            ->select('COUNT(b.id)')
            ->from(DoctrineBook::class, 'b')
            ->where('b.description IS NOT NULL')
            ->andWhere('b.description != :empty')
            ->setParameter('empty', '')
            ->getQuery()
            ->getSingleScalarResult();

        $booksWithoutDescription = $totalBooks - $booksWithDescription;

        $createdToday = (int) $this->em->createQueryBuilder()
            ->select('COUNT(b.id)')
            ->from(DoctrineBook::class, 'b')
            ->where('b.createdAt >= :today')
            ->setParameter('today', $todayStart)
            ->getQuery()
            ->getSingleScalarResult();

        $updatedToday = (int) $this->em->createQueryBuilder()
            ->select('COUNT(b.id)')
            ->from(DoctrineBook::class, 'b')
            ->where('b.updatedAt >= :today')
            ->setParameter('today', $todayStart)
            ->getQuery()
            ->getSingleScalarResult();

        $authorsCount = [];
        $authorBooks = $this->em->createQueryBuilder()
            ->select('b.author, COUNT(b.id) as bookCount')
            ->from(DoctrineBook::class, 'b')
            ->groupBy('b.author')
            ->getQuery()
            ->getResult();

        foreach ($authorBooks as $row) {
            $authorsCount[$row['author']] = (int) $row['bookCount'];
        }
        arsort($authorsCount);

        $oldestBook = $this->em->createQueryBuilder()
            ->select('b')
            ->from(DoctrineBook::class, 'b')
            ->orderBy('b.createdAt', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $newestBook = $this->em->createQueryBuilder()
            ->select('b')
            ->from(DoctrineBook::class, 'b')
            ->orderBy('b.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $oldestBookTitle = $oldestBook !== null ? $oldestBook->getTitle() : null;
        $newestBookTitle = $newestBook !== null ? $newestBook->getTitle() : null;

        return [
            'totalBooks' => $totalBooks,
            'booksWithDescription' => $booksWithDescription,
            'booksWithoutDescription' => $booksWithoutDescription,
            'createdToday' => $createdToday,
            'updatedToday' => $updatedToday,
            'authorsCount' => $authorsCount,
            'oldestBookTitle' => $oldestBookTitle,
            'newestBookTitle' => $newestBookTitle,
        ];
    }

    private function toDomain(DoctrineBook $doctrine): DomainBook
    {
        return new DomainBook(
            id: $doctrine->getId(),
            title: new Title($doctrine->getTitle()),
            author: $doctrine->getAuthor(),
            description: $doctrine->getDescription(),
            createdAt: $doctrine->getCreatedAt(),
        );
    }

    private function toDoctrine(DomainBook $domain): DoctrineBook
    {
        $doctrine = new DoctrineBook();
        $doctrine->setId($domain->getId());
        $doctrine->setTitle($domain->getTitle()->value);
        $doctrine->setAuthor($domain->getAuthor());
        $doctrine->setDescription($domain->getDescription());
        $doctrine->setCreatedAt($domain->getCreatedAt());
        $doctrine->setUpdatedAt($domain->getUpdatedAt());
        return $doctrine;
    }
}
