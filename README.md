# symfony-media-api

A production-style **REST API for media content management** — articles, episodes, collections and
tags — built with **Symfony 8.1** and **API Platform 4**. It demonstrates JWT authentication,
serialization groups, custom state processors, enum-based status management, filtering/pagination,
and a full automated test suite.

> The domain (articles / episodes / collections) deliberately mirrors the kind of CMS backend used by
> broadcasters and media platforms. See [`CONTEXT.md`](CONTEXT.md) for the design rationale and the
> target-company mapping.

## Tech stack

| Tool | Version | Role |
|------|---------|------|
| PHP | 8.4 | Language (enums, readonly, typed properties) |
| Symfony | 8.1 | Framework |
| API Platform | 4.3 | REST + OpenAPI/Swagger, filters, state processors |
| Doctrine ORM | 3.x | Persistence |
| Lexik JWT | 3.x | JWT authentication |
| SQLite | — | Local dev + test database (zero-config) |
| PostgreSQL | 16 | Production-like target (docker-compose / CI) |
| PHPUnit | 13.x | Testing |
| Zenstruck Foundry | 2.x | Test/fixture factories |

## Features

- **CRUD** for Article, Episode, Collection, Tag via API Platform resources.
- **JWT auth**: public read, `ROLE_ADMIN`-only writes (enforced per operation).
- **Serialization groups**: lean list payloads (`*:list`) vs full detail (`*:read`); write groups (`*:write`).
- **State processors**: auto-generate slugs; set/clear `publishedAt` on Article status changes.
- **Enums**: `ArticleStatus`, `EpisodeStatus`, `CollectionType` with domain helpers (`isPublic()`, `label()`).
- **Filters**: search (partial/exact), enum status, and ordering; pagination (20/page, max 100).
- **OpenAPI docs** + Swagger UI at `/api/docs`.

## Quick start (Laravel Herd, Windows)

```bash
# 1. Install dependencies
composer install

# 2. Generate the JWT keypair.
#    On Windows the OpenSSL config must be pointed at Herd's openssl.cnf first:
#    PowerShell: $env:OPENSSL_CONF = "C:\Users\<you>\.config\herd\bin\php84\extras\ssl\openssl.cnf"
php bin/console lexik:jwt:generate-keypair --skip-if-exists

# 3. Create the SQLite database and run migrations
php bin/console doctrine:migrations:migrate --no-interaction

# 4. (Optional) Load sample data
php bin/console doctrine:fixtures:load --no-interaction
```

Point Herd's document root at `public/` — the API is then served at
`https://symfony-media-api.test`, with Swagger UI at `https://symfony-media-api.test/api/docs`.
(Or run `symfony serve` / `php -S` against `public/`.)

The fixtures create two known accounts:

| Email | Password | Roles |
|-------|----------|-------|
| `admin@example.com` | `password` | `ROLE_ADMIN` |
| `user@example.com` | `password` | `ROLE_USER` |

## Running the tests

```bash
php bin/phpunit
```

Tests use a dedicated SQLite database (`var/test.db`, configured in `.env.test`) and
`dama/doctrine-test-bundle` wraps each test in a transaction that is rolled back, so runs are
isolated and repeatable. Create the test DB once with:

```bash
php bin/console doctrine:migrations:migrate --env=test --no-interaction
```

## API examples

Base URL below is `https://symfony-media-api.test`. Responses are JSON-LD (Hydra) by default.

### Authenticate (get a JWT)

```bash
curl -X POST https://symfony-media-api.test/api/auth/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"admin@example.com","password":"password"}'
# => {"token":"eyJ0eXAiOiJKV1QiL..."}
```

Store it:

```bash
TOKEN=$(curl -s -X POST https://symfony-media-api.test/api/auth/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"admin@example.com","password":"password"}' | jq -r .token)
```

### List articles (public)

```bash
curl https://symfony-media-api.test/api/articles
```

### Get a single article

```bash
curl https://symfony-media-api.test/api/articles/{id}
```

### Create an article (admin only — slug is generated automatically)

```bash
curl -X POST https://symfony-media-api.test/api/articles \
  -H "Authorization: Bearer $TOKEN" \
  -H 'Content-Type: application/json' \
  -d '{"title":"Hello World","body":"Lorem ipsum","author":"Stefano","status":"published"}'
```

### Filter and sort

```bash
# Only published articles, newest first
curl 'https://symfony-media-api.test/api/articles?status=published&order[publishedAt]=desc'

# Partial title search
curl 'https://symfony-media-api.test/api/articles?title=hello'

# Episodes by series and status
curl 'https://symfony-media-api.test/api/episodes?seriesName=The%20Daily&status=available'
```

### Paginate

```bash
curl 'https://symfony-media-api.test/api/articles?page=2'
```

## Project layout

```
src/
  Entity/            Article, Episode, Collection, Tag, User + enums
  Repository/        Doctrine repositories with query helpers
  State/Processor/   Slug + publishedAt processors (API Platform)
  Factory/           Foundry factories
  DataFixtures/      AppFixtures (sample data)
config/packages/     api_platform, doctrine, security, lexik_jwt, nelmio_cors...
tests/               ApiTestCase base + Api/ and Unit/ suites
migrations/          Doctrine migrations (SQLite-targeted)
```

## Notes

- **Versions**: this implementation runs on Symfony 8.1 / API Platform 4 / PHP 8.4. `CONTEXT.md`
  was originally drafted against Symfony 7 / API Platform 3; the concepts are unchanged.
- **Database**: dev and tests use SQLite for zero-friction setup. `docker-compose.yml` provides
  PostgreSQL for a production-like environment. The committed migrations are SQLite-targeted — for
  PostgreSQL, regenerate migrations (`doctrine:migrations:diff`) against that platform.
- **Windows/OpenSSL**: JWT key generation needs `OPENSSL_CONF` pointed at Herd's `openssl.cnf`
  (see Quick start).
