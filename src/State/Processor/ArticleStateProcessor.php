<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Article;
use App\Entity\ArticleStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Handles business logic when creating or updating an Article:
 * - Auto-generates slug from title
 * - Sets publishedAt when status changes to Published
 */
final class ArticleStateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SluggerInterface       $slugger,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Article
    {
        /** @var Article $data */

        // Auto-generate slug from title if not set
        if (empty($data->getSlug())) {
            $slug = strtolower($this->slugger->slug($data->getTitle())->toString());
            $data->setSlug($slug);
        }

        // Set publishedAt when transitioning to Published
        if ($data->getStatus() === ArticleStatus::Published && $data->getPublishedAt() === null) {
            $data->setPublishedAt(new \DateTimeImmutable());
        }

        // Clear publishedAt when moving back to Draft or Archived
        if ($data->getStatus() !== ArticleStatus::Published) {
            $data->setPublishedAt(null);
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}
