<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Collection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Collection>
 */
class CollectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Collection::class);
    }

    /** @return Collection[] */
    public function findPublished(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.isPublished = true')
            ->orderBy('c.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findBySlug(string $slug): ?Collection
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
