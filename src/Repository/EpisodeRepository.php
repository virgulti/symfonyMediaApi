<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Episode;
use App\Entity\EpisodeStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Episode>
 */
class EpisodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Episode::class);
    }

    /** @return Episode[] */
    public function findAvailable(): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.status = :status')
            ->setParameter('status', EpisodeStatus::Available)
            ->orderBy('e.broadcastAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findBySlug(string $slug): ?Episode
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
