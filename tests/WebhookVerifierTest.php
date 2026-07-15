<?php

namespace SubdomainTo\Tests;

use PHPUnit\Framework\TestCase;
use SubdomainTo\Exception\InvalidWebhookException;
use SubdomainTo\WebhookVerifier;

final class WebhookVerifierTest extends TestCase
{
    private const BODY = '{"id":"evt_1","event":"hostname.created","created_at":"2026-07-15T10:00:00+00:00","data":{"id":"host_1"}}';

    public function testItVerifiesAndParsesAnEvent(): void
    {
        $timestamp = 1_752_573_600;
        $signature = hash_hmac('sha256', $timestamp.'.'.self::BODY, 'secret');
        $event = (new WebhookVerifier())->verify(self::BODY, 't='.$timestamp.',v1='.$signature, 'secret', $timestamp + 30);
        self::assertSame('hostname.created', $event->event());
        self::assertSame('host_1', $event->data()['id']);
    }

    public function testItRejectsAlteredBodies(): void
    {
        $timestamp = 1_752_573_600;
        $signature = hash_hmac('sha256', $timestamp.'.'.self::BODY, 'secret');
        $this->expectException(InvalidWebhookException::class);
        (new WebhookVerifier())->verify(self::BODY.' ', 't='.$timestamp.',v1='.$signature, 'secret', $timestamp);
    }

    public function testItRejectsStaleSignatures(): void
    {
        $timestamp = 1_752_573_600;
        $signature = hash_hmac('sha256', $timestamp.'.'.self::BODY, 'secret');
        $this->expectException(InvalidWebhookException::class);
        (new WebhookVerifier(300))->verify(self::BODY, 't='.$timestamp.',v1='.$signature, 'secret', $timestamp + 301);
    }

    public function testItRejectsMalformedHeaders(): void
    {
        $this->expectException(InvalidWebhookException::class);
        (new WebhookVerifier())->verify(self::BODY, 'invalid', 'secret', 1_752_573_600);
    }

    public function testItRejectsSignedInvalidPayloads(): void
    {
        $timestamp = 1_752_573_600;
        $body = '{"event":"hostname.created"}';
        $signature = hash_hmac('sha256', $timestamp.'.'.$body, 'secret');
        $this->expectException(InvalidWebhookException::class);
        (new WebhookVerifier())->verify($body, 't='.$timestamp.',v1='.$signature, 'secret', $timestamp);
    }
}
