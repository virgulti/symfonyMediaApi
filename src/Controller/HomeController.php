<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController
{
    #[Route('/', name: 'homepage', methods: ['GET'])]
    public function __invoke(): Response
    {
        $groups = [
            'Articles' => [
                ['GET', '/api/articles', 'List articles', 'JSON-LD', false, true],
                ['GET', '/api/articles/{id}', 'Get a single article', 'JSON-LD', false, false],
                ['POST', '/api/articles', 'Create an article', 'JSON-LD', true, false],
                ['PATCH', '/api/articles/{id}', 'Update an article', 'JSON-LD', true, false],
                ['DELETE', '/api/articles/{id}', 'Delete an article', '204 No Content', true, false],
            ],
            'Episodes' => [
                ['GET', '/api/episodes', 'List episodes', 'JSON-LD', false, true],
                ['GET', '/api/episodes/{id}', 'Get a single episode', 'JSON-LD', false, false],
                ['POST', '/api/episodes', 'Create an episode', 'JSON-LD', true, false],
                ['PATCH', '/api/episodes/{id}', 'Update an episode', 'JSON-LD', true, false],
                ['DELETE', '/api/episodes/{id}', 'Delete an episode', '204 No Content', true, false],
            ],
            'Collections' => [
                ['GET', '/api/collections', 'List collections', 'JSON-LD', false, true],
                ['GET', '/api/collections/{id}', 'Get a single collection', 'JSON-LD', false, false],
                ['POST', '/api/collections', 'Create a collection', 'JSON-LD', true, false],
                ['PATCH', '/api/collections/{id}', 'Update a collection', 'JSON-LD', true, false],
                ['DELETE', '/api/collections/{id}', 'Delete a collection', '204 No Content', true, false],
            ],
            'Tags' => [
                ['GET', '/api/tags', 'List tags', 'JSON-LD', false, true],
                ['GET', '/api/tags/{id}', 'Get a single tag', 'JSON-LD', false, false],
                ['POST', '/api/tags', 'Create a tag', 'JSON-LD', true, false],
            ],
            'Auth & docs' => [
                ['POST', '/api/auth/login', 'Log in, returns a JWT', 'JSON', false, false],
                ['GET', '/api/docs.jsonopenapi', 'OpenAPI specification', 'JSON', false, false],
                ['GET', '/api/docs.jsonld', 'API entrypoint / Hydra docs', 'JSON-LD', false, false],
            ],
        ];

        $rows = '';
        foreach ($groups as $group => $endpoints) {
            $rows .= sprintf('<tr class="group"><th colspan="5">%s</th></tr>', htmlspecialchars($group));
            foreach ($endpoints as [$method, $path, $description, $output, $authRequired, $clickable]) {
                $pathCell = $clickable
                    ? sprintf('<a href="%1$s"><code>%1$s</code></a>', htmlspecialchars($path))
                    : sprintf('<code>%s</code>', htmlspecialchars($path));
                $rows .= sprintf(
                    '<tr><td><span class="method %s">%s</span></td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                    strtolower($method),
                    htmlspecialchars($method),
                    $pathCell,
                    htmlspecialchars($description),
                    htmlspecialchars($output),
                    $authRequired
                        ? '<span class="auth yes">JWT (ROLE_ADMIN)</span>'
                        : '<span class="auth no">Public</span>',
                );
            }
        }

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>symfony-media-api</title>
<style>
    body { font-family: system-ui, sans-serif; max-width: 900px; margin: 2rem auto; padding: 0 1rem; color: #1a1a1a; }
    h1 { margin-bottom: 0.25rem; }
    p.lead { color: #555; margin-top: 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 1.5rem; }
    th, td { text-align: left; padding: 0.5rem 0.75rem; border-bottom: 1px solid #e0e0e0; }
    tr.group th { background: #f4f4f5; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.03em; color: #444; }
    code { background: #f4f4f5; padding: 0.1rem 0.35rem; border-radius: 4px; font-size: 0.9em; }
    td a { text-decoration: none; }
    td a code { color: #2563eb; }
    td a:hover code { text-decoration: underline; }
    .method { display: inline-block; min-width: 3.2rem; text-align: center; padding: 0.1rem 0.4rem; border-radius: 4px; font-size: 0.8em; font-weight: 600; color: #fff; }
    .method.get { background: #2563eb; }
    .method.post { background: #16a34a; }
    .method.patch { background: #d97706; }
    .method.delete { background: #dc2626; }
    .auth.yes { color: #b91c1c; font-weight: 600; }
    .auth.no { color: #15803d; }
    footer { margin-top: 2rem; font-size: 0.85rem; color: #777; }
    footer a { color: inherit; }
</style>
</head>
<body>
<h1>symfony-media-api</h1>
<p class="lead">REST API for media content management &mdash; articles, episodes, collections and tags.</p>
<table>
<thead><tr><th>Method</th><th>Endpoint</th><th>Description</th><th>Output</th><th>Auth</th></tr></thead>
<tbody>
{$rows}
</tbody>
</table>
<footer>
    Log in via <code>POST /api/auth/login</code> with <code>{"email": "...", "password": "..."}</code>
    to get a JWT, then send it as <code>Authorization: Bearer &lt;token&gt;</code> on write requests.
    See the project <code>README.md</code> for full curl examples.
</footer>
</body>
</html>
HTML;

        return new Response($html);
    }
}
