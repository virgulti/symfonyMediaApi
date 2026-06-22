<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Article;
use App\Entity\ArticleStatus;
use Doctrine\ORM\EntityManagerInterface;

class ArticleApiTest extends ApiTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
    }

    public function test_get_articles_returns_empty_list(): void
    {
        $response = static::createClient()->request('GET', '/api/articles');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context'          => '/api/contexts/Article',
            'hydra:totalItems'  => 0,
        ]);
    }

    public function test_create_article_requires_authentication(): void
    {
        static::createClient()->request('POST', '/api/articles', [
            'json' => [
                'title'  => 'Test Article',
                'body'   => 'Content here',
                'author' => 'Stefano',
            ],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function test_get_article_returns_correct_fields(): void
    {
        // Create article directly via EM for testing
        $article = new Article();
        $article->setTitle('Test Title');
        $article->setSlug('test-title');
        $article->setBody('Test body content');
        $article->setAuthor('Test Author');
        $article->setStatus(ArticleStatus::Published);
        $article->setPublishedAt(new \DateTimeImmutable());

        $this->em->persist($article);
        $this->em->flush();

        $response = static::createClient()->request(
            'GET',
            '/api/articles/' . $article->getId()
        );

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'title'  => 'Test Title',
            'author' => 'Test Author',
            'status' => 'published',
        ]);

        // Body only in detail view
        $data = $response->toArray();
        $this->assertArrayHasKey('body', $data);
    }

    public function test_get_article_list_does_not_include_body(): void
    {
        $article = new Article();
        $article->setTitle('List Test');
        $article->setSlug('list-test');
        $article->setBody('Body content should not appear in list');
        $article->setAuthor('Author');
        $article->setStatus(ArticleStatus::Published);
        $article->setPublishedAt(new \DateTimeImmutable());

        $this->em->persist($article);
        $this->em->flush();

        $response = static::createClient()->request('GET', '/api/articles');
        $data     = $response->toArray();

        $firstItem = $data['hydra:member'][0] ?? [];
        $this->assertArrayNotHasKey('body', $firstItem);
    }
}
