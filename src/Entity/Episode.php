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
use App\Repository\EpisodeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EpisodeRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['episode:list']],
            paginationItemsPerPage: 20,
        ),
        new Get(
            normalizationContext: ['groups' => ['episode:read']],
        ),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            normalizationContext: ['groups' => ['episode:read']],
            denormalizationContext: ['groups' => ['episode:write']],
            processor: EpisodeStateProcessor::class,
        ),
        new Patch(
            security: "is_granted('ROLE_ADMIN')",
            denormalizationContext: ['groups' => ['episode:write']],
            processor: EpisodeStateProcessor::class,
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
        ),
    ],
)]
#[ApiFilter(SearchFilter::class, properties: [
    'title'      => 'partial',
    'seriesName' => 'exact',
    'status'     => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: ['broadcastAt', 'episodeNumber', 'createdAt'])]
class Episode
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Groups(['episode:list', 'episode:read'])]
    private ?Uuid $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['episode:list', 'episode:read', 'episode:write'])]
    private string $title = '';

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['episode:list', 'episode:read'])]
    private string $slug = '';

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Groups(['episode:read', 'episode:write'])]
    private string $description = '';

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['episode:list', 'episode:read', 'episode:write'])]
    private int $duration;

    #[ORM\Column(length: 255)]
    #[Groups(['episode:list', 'episode:read', 'episode:write'])]
    private string $seriesName = '';

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['episode:list', 'episode:read', 'episode:write'])]
    private int $episodeNumber;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['episode:list', 'episode:read', 'episode:write'])]
    private int $seasonNumber;

    #[ORM\Column(nullable: true)]
    #[Groups(['episode:list', 'episode:read'])]
    private ?\DateTimeImmutable $broadcastAt = null;

    #[ORM\Column(length: 50, enumType: EpisodeStatus::class)]
    #[Groups(['episode:list', 'episode:read', 'episode:write'])]
    private EpisodeStatus $status = EpisodeStatus::Scheduled;

    #[ORM\Column]
    #[Groups(['episode:read'])]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\ManyToMany(targetEntity: Tag::class)]
    #[Groups(['episode:list', 'episode:read', 'episode:write'])]
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
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): static { $this->description = $description; return $this; }
    public function getDuration(): int { return $this->duration; }
    public function setDuration(int $duration): static { $this->duration = $duration; return $this; }
    public function getSeriesName(): string { return $this->seriesName; }
    public function setSeriesName(string $seriesName): static { $this->seriesName = $seriesName; return $this; }
    public function getEpisodeNumber(): int { return $this->episodeNumber; }
    public function setEpisodeNumber(int $episodeNumber): static { $this->episodeNumber = $episodeNumber; return $this; }
    public function getSeasonNumber(): int { return $this->seasonNumber; }
    public function setSeasonNumber(int $seasonNumber): static { $this->seasonNumber = $seasonNumber; return $this; }
    public function getBroadcastAt(): ?\DateTimeImmutable { return $this->broadcastAt; }
    public function setBroadcastAt(?\DateTimeImmutable $broadcastAt): static { $this->broadcastAt = $broadcastAt; return $this; }
    public function getStatus(): EpisodeStatus { return $this->status; }
    public function setStatus(EpisodeStatus $status): static { $this->status = $status; return $this; }
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
