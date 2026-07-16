<?php
namespace SubdomainTo\Resource;
use SubdomainTo\Model\CollectionResult;
use SubdomainTo\Model\Domain;
final class DomainsResource extends Resource
{
    public function list(int $limit = 25, ?string $cursor = null): CollectionResult
    {
        $response = $this->client->send('GET', '/domains', ['limit' => $limit, 'cursor' => $cursor]);
        $items = array_map(function (array $item): Domain { return new Domain($item); }, is_array($response['data'] ?? null) ? $response['data'] : []);
        $meta = is_array($response['meta'] ?? null) ? $response['meta'] : [];
        return new CollectionResult($items, (bool) ($meta['has_more'] ?? false), isset($meta['next_cursor']) ? (string) $meta['next_cursor'] : null);
    }
    public function create(string $hostname, string $idempotencyKey, ?string $destinationId = null): Domain
    {
        $body = ['hostname' => $hostname]; if ($destinationId !== null) $body['destination_id'] = $destinationId;
        return new Domain($this->data($this->client->send('POST', '/domains', [], $body, $idempotencyKey)));
    }
    public function get(string $id): Domain { return new Domain($this->data($this->client->send('GET', '/domains/'.$this->id($id)))); }
    public function delete(string $id): Domain { return new Domain($this->data($this->client->send('DELETE', '/domains/'.$this->id($id)))); }
}
