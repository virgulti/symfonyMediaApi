<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Tests\ApiTestCase;

class AuthApiTest extends ApiTestCase
{
    public function test_login_returns_token_for_valid_credentials(): void
    {
        $this->createUser('admin@example.com', 'password', ['ROLE_ADMIN']);

        $response = static::createClient()->request('POST', '/api/auth/login', [
            'json' => ['email' => 'admin@example.com', 'password' => 'password'],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('token', $response->toArray());
    }

    public function test_login_with_wrong_password_returns_401(): void
    {
        $this->createUser('admin@example.com', 'password', ['ROLE_ADMIN']);

        static::createClient()->request('POST', '/api/auth/login', [
            'json' => ['email' => 'admin@example.com', 'password' => 'wrong'],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function test_admin_can_create_article_and_slug_is_generated(): void
    {
        [$client] = $this->createAuthenticatedClient('admin@example.com', ['ROLE_ADMIN']);

        $response = $client->request('POST', '/api/articles', [
            'json' => [
                'title'  => 'My First Article',
                'body'   => 'Some body content',
                'author' => 'Stefano',
                'status' => 'draft',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            'title' => 'My First Article',
            'slug'  => 'my-first-article',
        ]);
    }

    public function test_anonymous_cannot_create_article(): void
    {
        static::createClient()->request('POST', '/api/articles', [
            'json' => ['title' => 'X', 'body' => 'Y', 'author' => 'Z'],
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function test_non_admin_cannot_create_article(): void
    {
        [$client] = $this->createAuthenticatedClient('user@example.com', ['ROLE_USER']);

        $client->request('POST', '/api/articles', [
            'json' => ['title' => 'X', 'body' => 'Y', 'author' => 'Z'],
        ]);

        $this->assertResponseStatusCodeSame(403);
    }
}
