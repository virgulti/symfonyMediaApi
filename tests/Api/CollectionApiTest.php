<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Entity\Collection;
use App\Entity\CollectionType;
use App\Tests\ApiTestCase;

class CollectionApiTest extends ApiTestCase
{
    public function test_get_collections_empty(): void
    {
        $response = static::createClient()->request('GET', '/api/collections');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context'          => '/api/contexts/Collection',
            'hydra:totalItems'  => 0,
        ]);
    }

    public function test_get_collection_detail(): void
    {
        // Create collection directly via EM for testing
        $collection = new Collection();
        $collection->setTitle('Test Collection');
        $collection->setSlug('test-collection');
        $collection->setDescription('Test description content');
        $collection->setCollectionType(CollectionType::Playlist);
        $collection->setIsPublished(true);

        $this->entityManager()->persist($collection);
        $this->entityManager()->flush();

        $response = static::createClient()->request(
            'GET',
            '/api/collections/' . $collection->getId()
        );

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'title'         => 'Test Collection',
            'collectionType'=> 'playlist',
        ]);
    }

    public function test_filter_by_type(): void
    {
        // Create 2 Playlist collections and 1 Series collection
        for ($i = 0; $i < 2; $i++) {
            $collection = new Collection();
            $collection->setTitle("Playlist Collection $i");
            $collection->setSlug("playlist-collection-$i");
            $collection->setDescription('Test description content');
            $collection->setCollectionType(CollectionType::Playlist);
            $collection->setIsPublished(true);

            $this->entityManager()->persist($collection);
        }

        $seriesCollection = new Collection();
        $seriesCollection->setTitle('Series Collection');
        $seriesCollection->setSlug('series-collection');
        $seriesCollection->setDescription('Test description content');
        $seriesCollection->setCollectionType(CollectionType::Series);
        $seriesCollection->setIsPublished(true);

        $this->entityManager()->persist($seriesCollection);
        $this->entityManager()->flush();

        $response = static::createClient()->request(
            'GET',
            '/api/collections?collectionType=playlist'
        );

        $data = $response->toArray();
        $this->assertJsonContains([
            '@context'          => '/api/contexts/Collection',
            'hydra:totalItems'  => 2,
        ]);
    }

    public function test_create_requires_admin(): void
    {
        static::createClient()->request('POST', '/api/collections', [
            'json' => [
                'title'         => 'Test Collection',
                'slug'          => 'test-collection',
                'description'   => 'Content here',
                'collectionType'=> 'playlist',
                'isPublished'   => true,
            ],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }
}
