<?php
namespace SubdomainTo\Resource;
use SubdomainTo\Model\WidgetSession;
final class WidgetResource extends Resource
{
    public function createSession(string $idempotencyKey, ?string $externalCustomerId = null, array $allowedOrigins = []): WidgetSession
    {
        $body = [];
        if ($externalCustomerId !== null) { $body['external_customer_id'] = $externalCustomerId; }
        if ($allowedOrigins !== []) { $body['allowed_origins'] = array_values($allowedOrigins); }
        return new WidgetSession($this->data($this->client->send('POST', '/widget-sessions', [], $body, $idempotencyKey)));
    }
    public function createDomain(string $widgetToken, string $hostname): \SubdomainTo\Model\Domain
    {
        return new \SubdomainTo\Model\Domain($this->data($this->client->send('POST', '/widget/domains', [], ['hostname' => $hostname], null, $widgetToken)));
    }
}
