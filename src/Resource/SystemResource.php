<?php
namespace SubdomainTo\Resource;
use SubdomainTo\Model\Health;
final class SystemResource extends Resource
{
    public function health(): Health { return new Health($this->client->send('GET', '/health', [], null, null, null, false)); }
}
