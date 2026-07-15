<?php
namespace SubdomainTo\Resource;
use SubdomainTo\Model\DomainZone;
final class DomainZonesResource extends Resource
{
    public function create(string $projectId, string $domain, string $idempotencyKey): DomainZone
    {
        return new DomainZone($this->data($this->client->send('POST', '/projects/'.$this->id($projectId).'/domain-zones', [], ['domain' => $domain], $idempotencyKey)));
    }
}
