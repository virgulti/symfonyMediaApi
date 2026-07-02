# symfony-media-api

A production-style **REST API for media content management** — articles, episodes, collections and
tags — built with **Symfony 8.1** and **API Platform 4**. It demonstrates JWT authentication,
serialization groups, custom state processors, enum-based status management, filtering/pagination,
and a full automated test suite.

## Tech stack

| Tool | Version | Role |
|------|---------|------|
| PHP | 8.4 | Language (enums, readonly, typed properties) |
| Symfony | 8.1 | Framework |
| API Platform | 4.3 | REST + OpenAPI, filters, state processors |
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
- **OpenAPI docs** in JSON at `/api/docs.jsonopenapi` (JSON-LD context at `/api/docs.jsonld`).

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

Link the project to Herd (only needed once per clone):

```bash
herd link --secure symfony-media-api
```

Herd auto-detects the `public/` document root for Symfony projects. The API is then served at
`https://symfony-media-api.test`. (Or run `symfony serve` / `php -S` against `public/`.)

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
curl 'https://symfony-media-api.test/api/articles?status=published&order%5BpublishedAt%5D=desc'

# Partial title search
curl 'https://symfony-media-api.test/api/articles?title=hello'

# Episodes by series and status
curl 'https://symfony-media-api.test/api/episodes?seriesName=The%20Daily&status=available'
```

> `curl` treats unescaped `[`/`]` in a URL as glob ranges and errors out even when the URL is
> quoted — URL-encode them (`order[publishedAt]` → `order%5BpublishedAt%5D`), or pass `-g` to
> disable curl's globbing.

### Paginate

```bash
curl 'https://symfony-media-api.test/api/articles?page=2'
```

### Verify write permissions are enforced (should fail)

```bash
USER_TOKEN=$(curl -s -X POST https://symfony-media-api.test/api/auth/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"user@example.com","password":"password"}' | jq -r .token)

curl -i -X POST https://symfony-media-api.test/api/articles \
  -H "Authorization: Bearer $USER_TOKEN" \
  -H 'Content-Type: application/json' \
  -d '{"title":"Should be blocked","body":"x","author":"Test","status":"draft"}'
# => HTTP/1.1 403 Forbidden
```

### Clean up a test article

```bash
curl -X DELETE https://symfony-media-api.test/api/articles/{id} \
  -H "Authorization: Bearer $TOKEN"
# => HTTP/1.1 204 No Content
```

### Windows / PowerShell

The examples above are bash syntax (line-continuation with `\`, `$(...)` command substitution,
single-quoted JSON bodies) — they work as-is in Git Bash or WSL, but not in native PowerShell.

In PowerShell, `curl` is aliased to `Invoke-WebRequest`, which doesn't understand the `-X`/`-H`/`-d`
flags used above, `\` isn't a line-continuation character (use `` ` `` instead), and `$(...)` doesn't
capture a JSON field the way it does in bash. You have two options:

- **Use the real curl binary** by calling `curl.exe` explicitly. PowerShell passes single-quoted
  strings to native executables as-is, so any `"` inside the JSON body must be escaped with `\"` —
  otherwise the argument gets mis-split and curl may misinterpret `[`/`]` in the body (or in a query
  string, per the note above) as URL globbing syntax (`bad range in URL`). Windows' native curl also
  uses the schannel TLS backend, which fails Herd's locally-issued certificate with
  `CRYPT_E_NO_REVOCATION_CHECK` (curl error 35) unless you add `--ssl-no-revoke`:
  ```powershell
  curl.exe --ssl-no-revoke -X POST https://symfony-media-api.test/api/auth/login -H "Content-Type: application/json" -d '{\"email\":\"admin@example.com\",\"password\":\"password\"}'

  curl.exe --ssl-no-revoke -X POST https://symfony-media-api.test/api/articles -H "Authorization: Bearer $token" -H "Content-Type: application/json" -d '{\"title\":\"Hello World\",\"body\":\"Lorem ipsum\",\"author\":\"Stefano\",\"status\":\"published\"}'
  ```

- **Use `Invoke-RestMethod`** (recommended) — no shell-quoting pitfalls, since the body is built as
  a PowerShell object, and query-string arrays/brackets are just hashtable keys:
  ```powershell
  $login = Invoke-RestMethod -Uri "https://symfony-media-api.test/api/auth/login" -Method Post `
      -ContentType "application/json" -Body (@{ email = "admin@example.com"; password = "password" } | ConvertTo-Json)
  $token = $login.token

  $article = @{
      title  = "Hello World"
      body   = "Lorem ipsum"
      author = "Stefano"
      status = "published"
  } | ConvertTo-Json
  Invoke-RestMethod -Uri "https://symfony-media-api.test/api/articles" -Method Post `
      -Headers @{ Authorization = "Bearer $token" } -ContentType "application/json" -Body $article

  Invoke-RestMethod -Uri "https://symfony-media-api.test/api/articles" -Method Get `
      -Body @{ status = "published"; "order[publishedAt]" = "desc" }
  ```

## Manual end-to-end test checklist

A quick pass to confirm the whole stack works after a fresh setup:

1. **Auth** — log in as `admin@example.com` and `user@example.com`, confirm both return a token.
2. **Read** — `GET /api/articles` and `/api/episodes` return data (fixtures loaded).
3. **Write as admin** — `POST /api/articles` succeeds; response has an auto-generated `slug` and,
   if `status: published`, a `publishedAt` timestamp.
4. **Write as non-admin** — the same `POST` with the `user@example.com` token returns `403`.
5. **Filter/sort/paginate** — `status`, `order[...]`, `title`, and `page` query params behave as
   documented above.
6. **Clean up** — `DELETE` any article created during testing.

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

- **Versions**: this implementation runs on Symfony 8.1 / API Platform 4 / PHP 8.4.
- **Database**: dev and tests use SQLite for zero-friction setup. `docker-compose.yml` provides
  PostgreSQL for a production-like environment. The committed migrations are SQLite-targeted — for
  PostgreSQL, regenerate migrations (`doctrine:migrations:diff`) against that platform.
- **Windows/OpenSSL**: JWT key generation needs `OPENSSL_CONF` pointed at Herd's `openssl.cnf`
  (see Quick start).
