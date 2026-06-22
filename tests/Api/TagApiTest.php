<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Entity\Tag;
use App\Tests\ApiTestCase;

class TagApiTest extends ApiTestCase
{
    public function test_get_tags_list(): void
    {
        // Create 3 tags directly via EM for testing
        for ($i = 0; $i < 3; $i++) {
            $tag = new Tag();
            $tag->setName("Tag $i");
            $tag->setSlug("tag-$i");

            $this->entityManager()->persist($tag);
        }
        $this->entityManager()->flush();

        $response = static::createClient()->request('GET', '/api/tags');

        $data = $response->toArray();
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context'          => '/api/contexts/Tag',
            'hydra:totalItems'  => 3,
        ]);
    }

    public function test_admin_can_create_tag_and_slug_generated(): void
    {
        [$client] = $this->createAuthenticatedClient();

        $response = $client->request('POST', '/api/tags', [
            'json' => [
                'name' => 'Breaking News',
            ],
        ]);

        $data = $response->toArray();
        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            'name'  => 'Breaking News',
            'slug'  => 'breaking-news',
        ]);
    }

    public function test_anonymous_cannot_create_tag(): void
    {
        static::createClient()->request('POST', '/api/tags', [
            'json' => [
                'name' => 'Breaking News',
            ],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }
}
