<?php
namespace SubdomainTo\Resource;
use SubdomainTo\Model\WebhookEndpoint;
final class WebhooksResource extends Resource
{
    public function create(string $url, string $idempotencyKey, array $events = ['*']): WebhookEndpoint
    {
        return new WebhookEndpoint($this->data($this->client->send('POST', '/webhook-endpoints', [], ['url' => $url, 'events' => array_values($events)], $idempotencyKey)));
    }
}
