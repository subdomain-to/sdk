<?php
namespace SubdomainTo\Resource;
use SubdomainTo\Model\CollectionResult;
use SubdomainTo\Model\Project;
final class ProjectsResource extends Resource
{
    public function list(int $limit = 25, ?string $cursor = null): CollectionResult
    {
        $response = $this->client->send('GET', '/projects', ['limit' => $limit, 'cursor' => $cursor]);
        $items = array_map(function (array $item): Project { return new Project($item); }, is_array($response['data'] ?? null) ? $response['data'] : []);
        $meta = is_array($response['meta'] ?? null) ? $response['meta'] : [];
        return new CollectionResult($items, (bool) ($meta['has_more'] ?? false), isset($meta['next_cursor']) ? (string) $meta['next_cursor'] : null);
    }
    public function create(string $name, string $slug, string $idempotencyKey): Project
    {
        return new Project($this->data($this->client->send('POST', '/projects', [], ['name' => $name, 'slug' => $slug], $idempotencyKey)));
    }
}
