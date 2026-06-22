<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\EpisodeStatus;
use PHPUnit\Framework\TestCase;

class EpisodeStatusTest extends TestCase
{
    public function test_available_is_public(): void
    {
        $this->assertTrue(EpisodeStatus::Available->isPublic());
        $this->assertFalse(EpisodeStatus::Scheduled->isPublic());
        $this->assertFalse(EpisodeStatus::Live->isPublic());
        $this->assertFalse(EpisodeStatus::Archived->isPublic());
    }

    public function test_label_returns_human_readable(): void
    {
        $this->assertSame('Scheduled', EpisodeStatus::Scheduled->label());
        $this->assertSame('Live', EpisodeStatus::Live->label());
        $this->assertSame('Available', EpisodeStatus::Available->label());
        $this->assertSame('Archived', EpisodeStatus::Archived->label());
    }

    public function test_from_string_value(): void
    {
        $this->assertSame(EpisodeStatus::Scheduled, EpisodeStatus::from('scheduled'));
        $this->assertSame(EpisodeStatus::Live, EpisodeStatus::from('live'));
        $this->assertSame(EpisodeStatus::Available, EpisodeStatus::from('available'));
        $this->assertSame(EpisodeStatus::Archived, EpisodeStatus::from('archived'));
    }

    public function test_invalid_value_throws(): void
    {
        $this->expectException(\ValueError::class);
        EpisodeStatus::from('invalid');
    }
}
