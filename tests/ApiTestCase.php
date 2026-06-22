<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase as BaseApiTestCase;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Project base class for API functional tests.
 *
 * Provides a persisted entity manager, helpers to create users and to obtain a
 * JWT-authenticated client. The DAMA\DoctrineTestBundle wraps every test in a
 * transaction that is rolled back, so persisted data does not leak between tests.
 */
abstract class ApiTestCase extends BaseApiTestCase
{
    // API Platform 5.0 will stop booting the kernel implicitly; opt in now to
    // keep current behaviour and avoid the deprecation notice.
    protected static ?bool $alwaysBootKernel = true;

    protected function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * @param list<string> $roles
     */
    protected function createUser(string $email, string $password = 'password', array $roles = []): User
    {
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = static::getContainer()->get('test.user_password_hasher');

        $user = new User();
        $user->setEmail($email);
        $user->setRoles($roles);
        $user->setPassword($hasher->hashPassword($user, $password));

        $em = $this->entityManager();
        $em->persist($user);
        $em->flush();

        return $user;
    }

    /**
     * Returns the bearer token for the given credentials by calling the login endpoint.
     */
    protected function login(string $email, string $password = 'password'): string
    {
        $response = static::createClient()->request('POST', '/api/auth/login', [
            'json' => ['email' => $email, 'password' => $password],
        ]);

        return $response->toArray()['token'];
    }

    /**
     * @param list<string> $roles
     * @return array{0: \ApiPlatform\Symfony\Bundle\Test\Client, 1: User}
     */
    protected function createAuthenticatedClient(string $email = 'admin@example.com', array $roles = ['ROLE_ADMIN']): array
    {
        $user  = $this->createUser($email, 'password', $roles);
        $token = $this->login($email);

        $client = static::createClient();
        $client->setDefaultOptions(['headers' => ['Authorization' => 'Bearer '.$token]]);

        return [$client, $user];
    }
}
