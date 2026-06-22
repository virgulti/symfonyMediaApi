<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Repository\CollectionRepository;
use App\State\Processor\CollectionStateProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A curated collection of Articles and Episodes.
 *
 * The domain models a "collection of media items"; to keep the Doctrine mapping
 * clean we expose two distinct ManyToMany relations (articles, episodes) instead
 * of a single polymorphic relation.
 */
#[ORM\Entity(repositoryClass: CollectionRepository::class)]
#[ORM\Table(name: 'media_collection')]
#[UniqueEntity('slug')]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['collection:list']],
            paginationItemsPerPage: 20,
        ),
        new Get(
            normalizationContext: ['groups' => ['collection:read']],
        ),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            normalizationContext: ['groups' => ['collection:read']],
            denormalizationContext: ['groups' => ['collection:write']],
            processor: CollectionStateProcessor::class,
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN')",
            denormalizationContext: ['groups' => ['collection:write']],
            processor: CollectionStateProcessor::class,
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'title'          => 'partial',
    'collectionType' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['title'])]
class Collection
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(['collection:list', 'collection:read'])]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['collection:list', 'collection:read', 'collection:write'])]
    private string $title = '';

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['collection:list', 'collection:read'])]
    private string $slug = '';

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['collection:read', 'collection:write'])]
    private string $description = '';

    #[ORM\Column(length: 50, enumType: CollectionType::class)]
    #[Groups(['collection:list', 'collection:read', 'collection:write'])]
    private CollectionType $collectionType = CollectionType::Playlist;

    #[ORM\Column]
    #[Groups(['collection:list', 'collection:read', 'collection:write'])]
    private bool $isPublished = false;

    /** @var DoctrineCollection<int, Article> */
    #[ORM\ManyToMany(targetEntity: Article::class)]
    #[ORM\JoinTable(name: 'collection_article')]
    #[Groups(['collection:read', 'collection:write'])]
    private DoctrineCollection $articles;

    /** @var DoctrineCollection<int, Episode> */
    #[ORM\ManyToMany(targetEntity: Episode::class)]
    #[ORM\JoinTable(name: 'collection_episode')]
    #[Groups(['collection:read', 'collection:write'])]
    private DoctrineCollection $episodes;

    /** @var DoctrineCollection<int, Tag> */
    #[ORM\ManyToMany(targetEntity: Tag::class)]
    #[ORM\JoinTable(name: 'collection_tag')]
    #[Groups(['collection:list', 'collection:read', 'collection:write'])]
    private DoctrineCollection $tags;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->episodes = new ArrayCollection();
        $this->tags     = new ArrayCollection();
    }

    public function getId(): ?Uuid { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function setTitle(string $title): static { $this->title = $title; return $this; }
    public function getSlug(): string { return $this->slug; }
    public function setSlug(string $slug): static { $this->slug = $slug; return $this; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }
    public function getCollectionType(): CollectionType { return $this->collectionType; }
    public function setCollectionType(CollectionType $collectionType): static { $this->collectionType = $collectionType; return $this; }
    public function isPublished(): bool { return $this->isPublished; }
    public function setIsPublished(bool $isPublished): static { $this->isPublished = $isPublished; return $this; }

    /** @return DoctrineCollection<int, Article> */
    public function getArticles(): DoctrineCollection { return $this->articles; }

    public function addArticle(Article $article): static
    {
        if (! $this->articles->contains($article)) {
            $this->articles->add($article);
        }
        return $this;
    }

    public function removeArticle(Article $article): static
    {
        $this->articles->removeElement($article);
        return $this;
    }

    /** @return DoctrineCollection<int, Episode> */
    public function getEpisodes(): DoctrineCollection { return $this->episodes; }

    public function addEpisode(Episode $episode): static
    {
        if (! $this->episodes->contains($episode)) {
            $this->episodes->add($episode);
        }
        return $this;
    }

    public function removeEpisode(Episode $episode): static
    {
        $this->episodes->removeElement($episode);
        return $this;
    }

    /** @return DoctrineCollection<int, Tag> */
    public function getTags(): DoctrineCollection { return $this->tags; }

    public function addTag(Tag $tag): static
    {
        if (! $this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);
        return $this;
    }
}
