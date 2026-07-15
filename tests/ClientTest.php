<?php

namespace SubdomainTo\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SubdomainTo\Client;
use SubdomainTo\Exception\NotFoundException;
use SubdomainTo\Exception\AuthenticationException;
use SubdomainTo\Exception\BadRequestException;
use SubdomainTo\Exception\ConflictException;
use SubdomainTo\Exception\ForbiddenException;
use SubdomainTo\Exception\ServerException;

final class ClientTest extends TestCase
{
    public function testItCoversEveryApiOperationAndBuildsExpectedRequests(): void
    {
        $project = ['id' => '01J00000000000000000000000', 'name' => 'Production', 'slug' => 'production', 'default_origin' => null];
        $origin = ['id' => '01J00000000000000000000001', 'url' => 'https://app.example.com', 'host' => 'app.example.com', 'port' => 443];
        $zone = ['id' => '01J00000000000000000000002', 'base_domain' => 'customers.example.com', 'status' => 'pending_dns', 'dns_status' => 'pending', 'certificate_status' => 'pending', 'error' => null, 'dns_records' => []];
        $hostname = ['id' => '01J00000000000000000000003', 'hostname' => 'portal.example.com', 'type' => 'exact', 'status' => 'pending_dns', 'dns_status' => 'pending', 'certificate_status' => 'pending', 'routing_status' => 'pending', 'dns_records' => [], 'origin' => $origin, 'error' => null, 'created_at' => '2026-07-15T10:00:00+00:00'];
        $responses = [
            ['status' => 'ok', 'service' => 'subdomain.to', 'version' => 'v1'],
            ['data' => [$project], 'meta' => ['has_more' => false, 'next_cursor' => null]], ['data' => $project],
            ['data' => $origin], ['data' => $zone],
            ['data' => [$hostname], 'meta' => ['has_more' => false, 'next_cursor' => null]], ['data' => $hostname], ['data' => $hostname], ['data' => $hostname],
            ['data' => ['id' => '01J00000000000000000000004', 'url' => 'https://example.com/hook', 'events' => ['*'], 'active' => true, 'secret' => 'secret']],
            ['data' => ['token' => 'jwt', 'expires_at' => '2026-07-15T10:30:00+00:00', 'expires_in' => 1800]], ['data' => $hostname],
            ['data' => ['active_hostnames' => 1, 'included_hostnames' => 5, 'bandwidth_gb' => 0.5, 'included_bandwidth_gb' => 100]],
            ['data' => ['id' => 'cs_123', 'url' => 'https://checkout.example.com']],
        ];
        $http = new QueueClient(array_map(static function (array $body): Response { return new Response(200, ['Content-Type' => 'application/json'], json_encode($body)); }, $responses));
        $factory = new Psr17Factory();
        $client = new Client('sdt_live_test', $http, $factory, $factory, 'https://api.example.test/v1');

        self::assertSame('ok', $client->system()->health()->status());
        self::assertCount(1, $client->projects()->list()->items());
        $client->projects()->create('Production', 'production', 'project-1');
        $client->origins()->create($project['id'], $origin['url'], true, 'origin-1');
        $client->domainZones()->create($project['id'], 'customers.example.com', 'zone-1');
        $client->hostnames()->list($project['id']);
        $client->hostnames()->create($project['id'], 'portal.example.com', 'hostname-1');
        $client->hostnames()->get($hostname['id']);
        $client->hostnames()->delete($hostname['id']);
        $client->webhooks()->create('https://example.com/hook', 'webhook-1');
        $session = $client->widget()->createSession($project['id'], 'widget-1');
        $client->widget()->createHostname($session->token(), 'portal.example.com');
        $client->usage()->get();
        $client->billing()->createCheckoutSession('growth');

        self::assertCount(14, $http->requests);
        self::assertSame('', $http->requests[0]->getHeaderLine('Authorization'));
        self::assertSame('Bearer sdt_live_test', $http->requests[1]->getHeaderLine('Authorization'));
        self::assertSame('project-1', $http->requests[2]->getHeaderLine('Idempotency-Key'));
        self::assertSame('limit=25', $http->requests[1]->getUri()->getQuery());
        self::assertSame(['name' => 'Production', 'slug' => 'production'], json_decode((string) $http->requests[2]->getBody(), true));
        self::assertSame('Bearer jwt', $http->requests[11]->getHeaderLine('Authorization'));
        self::assertSame('/v1/billing/checkout-session', $http->requests[13]->getUri()->getPath());
    }

    public function testItMapsEveryDocumentedHttpErrorClass(): void
    {
        $cases = [400 => BadRequestException::class, 401 => AuthenticationException::class, 403 => ForbiddenException::class, 404 => NotFoundException::class, 409 => ConflictException::class, 500 => ServerException::class];
        $factory = new Psr17Factory();
        foreach ($cases as $status => $class) {
            $http = new QueueClient([new Response($status, [], json_encode(['error' => ['code' => 'error_'.$status, 'message' => 'Failure']]))]);
            $client = new Client('key', $http, $factory, $factory);
            try { $client->usage()->get(); self::fail('Expected '.$class); }
            catch (\Throwable $exception) { self::assertInstanceOf($class, $exception); }
        }
    }

    public function testItMapsStructuredErrors(): void
    {
        $http = new QueueClient([new Response(404, ['X-Request-Id' => 'req_123'], json_encode(['error' => ['code' => 'not_found', 'message' => 'Missing']]))]);
        $factory = new Psr17Factory();
        $client = new Client('key', $http, $factory, $factory);
        try { $client->hostnames()->get('missing'); self::fail('Expected an exception.'); }
        catch (NotFoundException $exception) {
            self::assertSame(404, $exception->statusCode());
            self::assertSame('not_found', $exception->apiCode());
            self::assertSame('req_123', $exception->requestId());
        }
    }

    public function testItRejectsInvalidIdempotencyKeysBeforeSending(): void
    {
        $http = new QueueClient([]); $factory = new Psr17Factory();
        $client = new Client('key', $http, $factory, $factory);
        $this->expectException(\InvalidArgumentException::class);
        $client->projects()->create('Name', 'slug', '');
    }
}

final class QueueClient implements ClientInterface
{
    public $requests = [];
    private $responses;
    public function __construct(array $responses) { $this->responses = $responses; }
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->requests[] = $request;
        if ($this->responses === []) { throw new \RuntimeException('No queued response.'); }
        return array_shift($this->responses);
    }
}
