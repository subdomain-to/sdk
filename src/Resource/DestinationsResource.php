<?php
namespace SubdomainTo\Resource;
use SubdomainTo\Model\CollectionResult;
use SubdomainTo\Model\Destination;
final class DestinationsResource extends Resource
{
    public function list(int $limit = 25, ?string $cursor = null): CollectionResult
    {
        $response = $this->client->send('GET', '/destinations', ['limit' => $limit, 'cursor' => $cursor]);
        $items = array_map(function (array $item): Destination { return new Destination($item); }, is_array($response['data'] ?? null) ? $response['data'] : []);
        $meta = is_array($response['meta'] ?? null) ? $response['meta'] : [];
        return new CollectionResult($items, (bool) ($meta['has_more'] ?? false), isset($meta['next_cursor']) ? (string) $meta['next_cursor'] : null);
    }
    public function create(string $url, string $idempotencyKey): Destination
    {
        return new Destination($this->data($this->client->send('POST', '/destinations', [], ['url' => $url], $idempotencyKey)));
    }
}
