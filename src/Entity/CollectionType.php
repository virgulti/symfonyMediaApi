<?php

declare(strict_types=1);

namespace App\Entity;

enum CollectionType: string
{
    case Playlist  = 'playlist';
    case Series    = 'series';
    case Spotlight = 'spotlight';
    case Editorial = 'editorial';

    public function label(): string
    {
        return match ($this) {
            self::Playlist  => 'Playlist',
            self::Series    => 'Series',
            self::Spotlight => 'Spotlight',
            self::Editorial => 'Editorial',
        };
    }
}
