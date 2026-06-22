<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\ArticleStatus;
use PHPUnit\Framework\TestCase;

class ArticleStatusTest extends TestCase
{
    public function test_published_is_public(): void
    {
        $this->assertTrue(ArticleStatus::Published->isPublic());
        $this->assertFalse(ArticleStatus::Draft->isPublic());
        $this->assertFalse(ArticleStatus::Archived->isPublic());
    }

    public function test_draft_can_transition_to_published(): void
    {
        $this->assertTrue(ArticleStatus::Draft->canTransitionTo(ArticleStatus::Published));
        $this->assertTrue(ArticleStatus::Draft->canTransitionTo(ArticleStatus::Archived));
    }

    public function test_published_can_only_transition_to_archived(): void
    {
        $this->assertTrue(ArticleStatus::Published->canTransitionTo(ArticleStatus::Archived));
        $this->assertFalse(ArticleStatus::Published->canTransitionTo(ArticleStatus::Draft));
    }

    public function test_archived_is_terminal(): void
    {
        $this->assertFalse(ArticleStatus::Archived->canTransitionTo(ArticleStatus::Draft));
        $this->assertFalse(ArticleStatus::Archived->canTransitionTo(ArticleStatus::Published));
    }

    public function test_from_string_value(): void
    {
        $this->assertSame(ArticleStatus::Draft, ArticleStatus::from('draft'));
        $this->assertSame(ArticleStatus::Published, ArticleStatus::from('published'));
    }

    public function test_invalid_value_throws(): void
    {
        $this->expectException(\ValueError::class);
        ArticleStatus::from('invalid');
    }
}
