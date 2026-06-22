<?php

declare(strict_types=1);

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Auto-generates the slug from the title when creating or updating a Collection.
 */
final class CollectionStateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SluggerInterface       $slugger,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Collection
    {
        /** @var Collection $data */
        if (empty($data->getSlug())) {
            $slug = strtolower($this->slugger->slug($data->getTitle())->toString());
            $data->setSlug($slug);
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}
