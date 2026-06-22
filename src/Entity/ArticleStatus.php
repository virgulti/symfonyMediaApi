<?php

declare(strict_types=1);

namespace App\Entity;

enum ArticleStatus: string
{
    case Draft     = 'draft';
    case Published = 'published';
    case Archived  = 'archived';

    public function isPublic(): bool
    {
        return $this === self::Published;
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match($this) {
            self::Draft     => in_array($newStatus, [self::Published, self::Archived]),
            self::Published => $newStatus === self::Archived,
            self::Archived  => false, // terminal state
        };
    }

    public function label(): string
    {
        return match($this) {
            self::Draft     => 'Draft',
            self::Published => 'Published',
            self::Archived  => 'Archived',
        };
    }
}
