<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Article;
use App\Entity\ArticleStatus;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Article>
 */
final class ArticleFactory extends PersistentObjectFactory
{
    public static function class(): string
    {
        return Article::class;
    }

    protected function defaults(): array
    {
        $title  = self::faker()->unique()->sentence(4);
        $status = self::faker()->randomElement(ArticleStatus::cases());

        return [
            'title'       => rtrim($title, '.'),
            'slug'        => (new AsciiSlugger())->slug($title)->lower()->toString(),
            'body'        => self::faker()->paragraphs(3, true),
            'author'      => self::faker()->name(),
            'status'      => $status,
            'publishedAt' => $status === ArticleStatus::Published
                ? \DateTimeImmutable::createFromMutable(self::faker()->dateTimeThisYear())
                : null,
        ];
    }
}
