<?php
namespace SubdomainTo\Resource;
use SubdomainTo\Model\CollectionResult;
use SubdomainTo\Model\Hostname;
final class HostnamesResource extends Resource
{
    public function list(string $projectId, int $limit = 25, ?string $cursor = null): CollectionResult
    {
        $response = $this->client->send('GET', '/projects/'.$this->id($projectId).'/hostnames', ['limit' => $limit, 'cursor' => $cursor]);
        $items = array_map(function (array $item): Hostname { return new Hostname($item); }, is_array($response['data'] ?? null) ? $response['data'] : []);
        $meta = is_array($response['meta'] ?? null) ? $response['meta'] : [];
        return new CollectionResult($items, (bool) ($meta['has_more'] ?? false), isset($meta['next_cursor']) ? (string) $meta['next_cursor'] : null);
    }
    public function create(string $projectId, string $hostname, string $idempotencyKey, ?string $zoneId = null, ?string $originId = null): Hostname
    {
        $body = ['hostname' => $hostname];
        if ($zoneId !== null) { $body['zone_id'] = $zoneId; }
        if ($originId !== null) { $body['origin_id'] = $originId; }
        return new Hostname($this->data($this->client->send('POST', '/projects/'.$this->id($projectId).'/hostnames', [], $body, $idempotencyKey)));
    }
    public function get(string $id): Hostname { return new Hostname($this->data($this->client->send('GET', '/hostnames/'.$this->id($id)))); }
    public function delete(string $id): Hostname { return new Hostname($this->data($this->client->send('DELETE', '/hostnames/'.$this->id($id)))); }
}
