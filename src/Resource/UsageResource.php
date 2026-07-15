<?php
namespace SubdomainTo\Resource;
use SubdomainTo\Model\Usage;
final class UsageResource extends Resource
{
    public function get(): Usage { return new Usage($this->data($this->client->send('GET', '/usage'))); }
}
