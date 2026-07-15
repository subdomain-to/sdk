<?php

namespace SubdomainTo;

use SubdomainTo\Exception\InvalidWebhookException;
use SubdomainTo\Model\WebhookEvent;

final class WebhookVerifier
{
    private $tolerance;
    public function __construct(int $tolerance = 300)
    {
        if ($tolerance < 0) { throw new \InvalidArgumentException('Webhook tolerance cannot be negative.'); }
        $this->tolerance = $tolerance;
    }

    public function verify(string $rawBody, string $signatureHeader, string $secret, ?int $now = null): WebhookEvent
    {
        if ($secret === '') { throw new InvalidWebhookException('The webhook secret cannot be empty.'); }
        $values = [];
        foreach (explode(',', $signatureHeader) as $part) {
            $pair = explode('=', trim($part), 2);
            if (count($pair) === 2) { $values[$pair[0]][] = $pair[1]; }
        }
        $timestamp = isset($values['t'][0]) && ctype_digit($values['t'][0]) ? (int) $values['t'][0] : null;
        $signatures = $values['v1'] ?? [];
        if ($timestamp === null || $signatures === []) { throw new InvalidWebhookException('The webhook signature header is malformed.'); }
        $now = $now ?? time();
        if (abs($now - $timestamp) > $this->tolerance) { throw new InvalidWebhookException('The webhook signature timestamp is outside the allowed tolerance.'); }
        $expected = hash_hmac('sha256', $timestamp.'.'.$rawBody, $secret);
        $valid = false;
        foreach ($signatures as $signature) { $valid = hash_equals($expected, $signature) || $valid; }
        if (!$valid) { throw new InvalidWebhookException('The webhook signature is invalid.'); }
        $payload = json_decode($rawBody, true);
        if (!is_array($payload) || !isset($payload['id'], $payload['event'], $payload['created_at']) || !is_array($payload['data'] ?? null)) {
            throw new InvalidWebhookException('The webhook payload is invalid.');
        }
        return new WebhookEvent($payload);
    }
}
