<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Tag;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Tag>
 */
final class TagFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Tag::class;
    }

    protected function defaults(): array
    {
        $name = self::faker()->unique()->word();

        return [
            'name' => $name,
            'slug' => (new AsciiSlugger())->slug($name)->lower()->toString(),
        ];
    }
}
