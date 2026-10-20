<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BookRepository::class)]
#[ORM\Table(name: 'book')]
class Book
{
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING, length: 36, unique: true)]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private ?string $id = null;

    #[Assert\NotBlank(message: "Title is required.")]
    #[Assert\Length(min: 1, max: 255, minMessage: "Title must be at least {{ limit }} character.", maxMessage: "Title cannot exceed {{ limit }} characters.")]
    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    #[Groups(['book:read', 'book:create'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::STRING, length: 32, unique: true)]
    private string $titleHash;

    #[Assert\NotBlank(message: "Author is required.")]
    #[Assert\Length(max: 200, maxMessage: "Author cannot exceed {{ limit }} characters.")]
    #[ORM\Column(type: Types::STRING, length: 200)]
    #[Groups(['book:read', 'book:create', 'book:update'])]
    private ?string $author = null;

    #[Assert\Length(max: 65535, maxMessage: "Description cannot exceed {{ limit }} characters.")]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['book:read', 'book:create', 'book:update'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['book:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['book:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->id = strtoupper(bin2hex(random_bytes(16)));
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
        if ($this->title !== null) {
            $this->titleHash = strtolower(md5(trim($this->title)));
        }
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        $this->titleHash = strtolower(md5(trim($title)));
        
        return $this;
    }

    public function getTitleHash(): string
    {
        return $this->titleHash;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;
        
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function toArray(array $groups = ['book:read']): array
    {
        $normalizer = new \Symfony\Component\Serializer\Normalizer\ObjectNormalizer();
        $dateTimeNormalizer = new \Symfony\Component\Serializer\Normalizer\DateTimeNormalizer();
        $encoder = new \Symfony\Component\Serializer\Encoder\JsonEncoder();
        $serializer = new \Symfony\Component\Serializer\Serializer([$dateTimeNormalizer, $normalizer], [$encoder]);
        
        return json_decode($serializer->serialize($this, 'json', ['groups' => $groups]), true);
    }
}
