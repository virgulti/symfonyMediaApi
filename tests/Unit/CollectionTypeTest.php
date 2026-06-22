<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\CollectionType;
use PHPUnit\Framework\TestCase;

class CollectionTypeTest extends TestCase
{
    public function test_cases_have_expected_values(): void
    {
        $this->assertSame('playlist', CollectionType::Playlist->value);
        $this->assertSame('series', CollectionType::Series->value);
        $this->assertSame('spotlight', CollectionType::Spotlight->value);
        $this->assertSame('editorial', CollectionType::Editorial->value);
    }

    public function test_label_returns_human_readable(): void
    {
        $this->assertSame('Playlist', CollectionType::Playlist->label());
        $this->assertSame('Series', CollectionType::Series->label());
        $this->assertSame('Spotlight', CollectionType::Spotlight->label());
        $this->assertSame('Editorial', CollectionType::Editorial->label());
    }

    public function test_from_string_value(): void
    {
        $this->assertSame(CollectionType::Playlist, CollectionType::from('playlist'));
        $this->assertSame(CollectionType::Series, CollectionType::from('series'));
        $this->assertSame(CollectionType::Spotlight, CollectionType::Spotlight->from('spotlight'));
        $this->assertSame(CollectionType::Editorial, CollectionType::from('editorial'));
    }

    public function test_invalid_value_throws(): void
    {
        $this->expectException(\ValueError::class);
        CollectionType::from('nope');
    }
}
