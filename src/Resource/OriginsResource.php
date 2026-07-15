<?php
namespace SubdomainTo\Resource;
use SubdomainTo\Model\Origin;
final class OriginsResource extends Resource
{
    public function create(string $projectId, string $url, bool $default, string $idempotencyKey): Origin
    {
        return new Origin($this->data($this->client->send('POST', '/projects/'.$this->id($projectId).'/origins', [], ['url' => $url, 'default' => $default], $idempotencyKey)));
    }
}
