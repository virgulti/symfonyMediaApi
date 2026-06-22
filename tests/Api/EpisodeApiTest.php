<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Entity\Episode;
use App\Entity\EpisodeStatus;
use App\Tests\ApiTestCase;

class EpisodeApiTest extends ApiTestCase
{
    public function test_get_episodes_returns_empty_list(): void
    {
        $response = static::createClient()->request('GET', '/api/episodes');

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context'          => '/api/contexts/Episode',
            'hydra:totalItems'  => 0,
        ]);
    }

    public function test_get_episode_returns_detail_fields(): void
    {
        // Create episode directly via EM for testing
        $episode = new Episode();
        $episode->setTitle('Test Episode');
        $episode->setSlug('test-episode');
        $episode->setDescription('Test description content');
        $episode->setDuration(60);
        $episode->setSeriesName('Test Series');
        $episode->setEpisodeNumber(1);
        $episode->setSeasonNumber(1);
        $episode->setStatus(EpisodeStatus::Available);

        $this->entityManager()->persist($episode);
        $this->entityManager()->flush();

        $response = static::createClient()->request(
            'GET',
            '/api/episodes/' . $episode->getId()
        );

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'title'  => 'Test Episode',
            'status' => 'available',
        ]);

        // Description only in detail view
        $data = $response->toArray();
        $this->assertArrayHasKey('description', $data);
    }

    public function test_episode_list_excludes_description(): void
    {
        $episode = new Episode();
        $episode->setTitle('List Test');
        $episode->setSlug('list-test');
        $episode->setDescription('Description content should not appear in list');
        $episode->setDuration(60);
        $episode->setSeriesName('Test Series');
        $episode->setEpisodeNumber(1);
        $episode->setSeasonNumber(1);
        $episode->setStatus(EpisodeStatus::Available);

        $this->entityManager()->persist($episode);
        $this->entityManager()->flush();

        $response = static::createClient()->request('GET', '/api/episodes');
        $data     = $response->toArray();

        $firstItem = $data['hydra:member'][0] ?? [];
        $this->assertArrayNotHasKey('description', $firstItem);
    }

    public function test_filter_by_status(): void
    {
        // Create 2 Available episodes and 1 Scheduled episode
        for ($i = 0; $i < 2; $i++) {
            $episode = new Episode();
            $episode->setTitle("Available Episode $i");
            $episode->setSlug("available-episode-$i");
            $episode->setDescription('Test description content');
            $episode->setDuration(60);
            $episode->setSeriesName('Test Series');
            $episode->setEpisodeNumber($i + 1);
            $episode->setSeasonNumber(1);
            $episode->setStatus(EpisodeStatus::Available);

            $this->entityManager()->persist($episode);
        }

        $scheduledEpisode = new Episode();
        $scheduledEpisode->setTitle('Scheduled Episode');
        $scheduledEpisode->setSlug('scheduled-episode');
        $scheduledEpisode->setDescription('Test description content');
        $scheduledEpisode->setDuration(60);
        $scheduledEpisode->setSeriesName('Test Series');
        $scheduledEpisode->setEpisodeNumber(3);
        $scheduledEpisode->setSeasonNumber(1);
        $scheduledEpisode->setStatus(EpisodeStatus::Scheduled);

        $this->entityManager()->persist($scheduledEpisode);
        $this->entityManager()->flush();

        $response = static::createClient()->request(
            'GET',
            '/api/episodes?status=available'
        );

        $data = $response->toArray();
        $this->assertJsonContains([
            '@context'          => '/api/contexts/Episode',
            'hydra:totalItems'  => 2,
        ]);
    }

    public function test_create_requires_admin(): void
    {
        static::createClient()->request('POST', '/api/episodes', [
            'json' => [
                'title'         => 'Test Episode',
                'slug'          => 'test-episode',
                'description'   => 'Content here',
                'duration'      => 60,
                'seriesName'    => 'Test Series',
                'episodeNumber' => 1,
                'seasonNumber'  => 1,
                'status'        => 'available',
            ],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }
}
