<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Factory\ArticleFactory;
use App\Factory\CollectionFactory;
use App\Factory\EpisodeFactory;
use App\Factory\TagFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Users: one admin and one regular account with known credentials.
        UserFactory::createOne(['email' => 'admin@example.com', 'roles' => ['ROLE_ADMIN']]);
        UserFactory::createOne(['email' => 'user@example.com', 'roles' => []]);
        UserFactory::createMany(3);

        // Tags.
        $tags = TagFactory::createMany(15);

        // Articles, each tagged with a random subset of tags.
        ArticleFactory::createMany(30, fn () => [
            'tags' => $this->pick($tags),
        ]);

        // Episodes, likewise.
        EpisodeFactory::createMany(20, fn () => [
            'tags' => $this->pick($tags),
        ]);

        // Collections referencing random articles and episodes.
        $articles = ArticleFactory::all();
        $episodes = EpisodeFactory::all();

        CollectionFactory::createMany(6, fn () => [
            'articles' => \array_slice($articles, 0, random_int(2, 6)),
            'episodes' => \array_slice($episodes, 0, random_int(1, 4)),
            'tags'     => $this->pick($tags),
        ]);
    }

    /**
     * Returns a random-sized random subset (1..4 items) of the given proxies.
     *
     * @template T
     * @param list<T> $items
     * @return list<T>
     */
    private function pick(array $items): array
    {
        shuffle($items);

        return \array_slice($items, 0, random_int(1, min(4, \count($items))));
    }
}
