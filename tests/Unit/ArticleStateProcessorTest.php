<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use App\Entity\Article;
use App\Entity\ArticleStatus;
use App\State\Processor\ArticleStateProcessor;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\Slugger\AsciiSlugger;

class ArticleStateProcessorTest extends TestCase
{
    private function processor(): ArticleStateProcessor
    {
        // A stub is enough: the processor only calls persist()/flush() (both void).
        $em = $this->createStub(EntityManagerInterface::class);

        return new ArticleStateProcessor($em, new AsciiSlugger());
    }

    private function operation(): Operation
    {
        // A concrete operation avoids mocking Operation (which has readonly properties).
        return new Get();
    }

    public function test_slug_is_generated_from_title(): void
    {
        $article = (new Article())->setTitle('Hello Beautiful World')->setBody('x')->setAuthor('a');

        $result = $this->processor()->process($article, $this->operation());

        $this->assertSame('hello-beautiful-world', $result->getSlug());
    }

    public function test_existing_slug_is_not_overwritten(): void
    {
        $article = (new Article())->setTitle('Hello World')->setSlug('custom-slug')->setBody('x')->setAuthor('a');

        $result = $this->processor()->process($article, $this->operation());

        $this->assertSame('custom-slug', $result->getSlug());
    }

    public function test_published_at_is_set_when_status_is_published(): void
    {
        $article = (new Article())->setTitle('T')->setBody('x')->setAuthor('a')->setStatus(ArticleStatus::Published);

        $result = $this->processor()->process($article, $this->operation());

        $this->assertNotNull($result->getPublishedAt());
    }

    public function test_published_at_is_cleared_when_not_published(): void
    {
        $article = (new Article())
            ->setTitle('T')->setBody('x')->setAuthor('a')
            ->setStatus(ArticleStatus::Draft)
            ->setPublishedAt(new \DateTimeImmutable());

        $result = $this->processor()->process($article, $this->operation());

        $this->assertNull($result->getPublishedAt());
    }
}
