<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\ArticleRepository;
use App\State\Processor\ArticleStateProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['article:list']],
            paginationItemsPerPage: 20,
        ),
        new Get(
            normalizationContext: ['groups' => ['article:read']],
        ),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            normalizationContext: ['groups' => ['article:read']],
            denormalizationContext: ['groups' => ['article:write']],
            processor: ArticleStateProcessor::class,
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN')",
            denormalizationContext: ['groups' => ['article:write']],
            processor: ArticleStateProcessor::class,
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'title'  => 'partial',
    'author' => 'exact',
    'status' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['publishedAt', 'title', 'createdAt'])]
class Article
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(['article:list', 'article:read'])]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['article:list', 'article:read', 'article:write'])]
    private string $title = '';

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['article:list', 'article:read'])]
    private string $slug = '';

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Groups(['article:read', 'article:write'])]
    private string $body = '';

    #[ORM\Column(length: 50, enumType: ArticleStatus::class)]
    #[Groups(['article:list', 'article:read', 'article:write'])]
    private ArticleStatus $status = ArticleStatus::Draft;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Groups(['article:list', 'article:read', 'article:write'])]
    private string $author = '';

    #[ORM\Column(nullable: true)]
    #[Groups(['article:list', 'article:read'])]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column]
    #[Groups(['article:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'articles')]
    #[Groups(['article:list', 'article:read', 'article:write'])]
    private Collection $tags;

    public function __construct()
    {
        $this->tags      = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?Uuid { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }
    public function getBody(): string { return $this->body; }
    public function setBody(string $body): static { $this->body = $body; return $this; }
    public function getStatus(): ArticleStatus { return $this->status; }
    public function setStatus(ArticleStatus $status): static { $this->status = $status; return $this; }
    public function getAuthor(): string { return $this->author; }
    public function setAuthor(string $author): static { $this->author = $author; return $this; }
    public function getPublishedAt(): ?\DateTimeImmutable { return $this->publishedAt; }
    public function setPublishedAt(?\DateTimeImmutable $publishedAt): static { $this->publishedAt = $publishedAt; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getTags(): Collection { return $this->tags; }

    public function addTag(Tag $tag): static
    {
        if (! $this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
        return $this;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
