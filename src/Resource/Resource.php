<?php
namespace SubdomainTo\Resource;
use SubdomainTo\Client;
abstract class Resource
{
    protected $client;
    final public function __construct(Client $client) { $this->client = $client; }
    final protected function id(string $value): string { return rawurlencode($value); }
    final protected function data(array $response): array { return is_array($response['data'] ?? null) ? $response['data'] : []; }
}
