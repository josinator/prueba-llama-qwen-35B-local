<?php declare(strict_types=1);

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
    ) {}

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
        return array_map(fn(DoctrineBook $db) => $this->toDomain($db), $doctrineBooks);
    }

    /** @return DomainBook[] */
    public function findByTitleHash(string $titleHash): array
    {
        $doctrineBooks = $this->em->getRepository(DoctrineBook::class)
            ->findBy(['titleHash' => $titleHash]);
        
        return array_map(fn(DoctrineBook $db) => $this->toDomain($db), $doctrineBooks);
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
