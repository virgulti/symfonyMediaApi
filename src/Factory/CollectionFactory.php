<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Collection;
use App\Entity\CollectionType;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Collection>
 */
final class CollectionFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Collection::class;
    }

    protected function defaults(): array
    {
        $title = self::faker()->unique()->sentence(2);

        return [
            'title'          => rtrim($title, '.'),
            'slug'           => (new AsciiSlugger())->slug($title)->lower()->toString(),
            'description'    => self::faker()->sentence(12),
            'collectionType' => self::faker()->randomElement(CollectionType::cases()),
            'isPublished'    => self::faker()->boolean(70),
        ];
    }
}
