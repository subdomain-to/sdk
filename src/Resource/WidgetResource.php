<?php
namespace SubdomainTo\Resource;
use SubdomainTo\Model\Hostname;
use SubdomainTo\Model\WidgetSession;
final class WidgetResource extends Resource
{
    public function createSession(string $projectId, string $idempotencyKey, ?string $externalCustomerId = null, array $allowedOrigins = []): WidgetSession
    {
        $body = ['project_id' => $projectId];
        if ($externalCustomerId !== null) { $body['external_customer_id'] = $externalCustomerId; }
        if ($allowedOrigins !== []) { $body['allowed_origins'] = array_values($allowedOrigins); }
        return new WidgetSession($this->data($this->client->send('POST', '/widget-sessions', [], $body, $idempotencyKey)));
    }
    public function createHostname(string $widgetToken, string $hostname): Hostname
    {
        return new Hostname($this->data($this->client->send('POST', '/widget/hostnames', [], ['hostname' => $hostname], null, $widgetToken)));
    }
}
