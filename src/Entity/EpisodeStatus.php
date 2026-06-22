<?php

declare(strict_types=1);

namespace App\Entity;

enum EpisodeStatus: string
{
    case Scheduled = 'scheduled';
    case Live      = 'live';
    case Available = 'available';
    case Archived  = 'archived';

    public function isPublic(): bool
    {
        return $this === self::Available;
    }

    public function label(): string
    {
        return match($this) {
            self::Scheduled => 'Scheduled',
            self::Live      => 'Live',
            self::Available => 'Available',
            self::Archived  => 'Archived',
        };
    }
}
