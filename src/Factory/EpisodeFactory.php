<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Episode;
use App\Entity\EpisodeStatus;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Episode>
 */
final class EpisodeFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Episode::class;
    }

    protected function defaults(): array
    {
        $title  = self::faker()->unique()->sentence(3);
        $status = self::faker()->randomElement(EpisodeStatus::cases());

        return [
            'title'         => rtrim($title, '.'),
            'slug'          => (new AsciiSlugger())->slug($title)->lower()->toString(),
            'description'   => self::faker()->paragraph(),
            'duration'      => self::faker()->numberBetween(120, 3600),
            'seriesName'    => self::faker()->randomElement(['Tech Today', 'Deep Dive', 'The Daily', 'Frontiers', 'Spotlight']),
            'episodeNumber' => self::faker()->numberBetween(1, 24),
            'seasonNumber'  => self::faker()->numberBetween(1, 5),
            'broadcastAt'   => \DateTimeImmutable::createFromMutable(self::faker()->dateTimeThisYear()),
            'status'        => $status,
        ];
    }
}
