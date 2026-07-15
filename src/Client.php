<?php

namespace SubdomainTo;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use SubdomainTo\Exception\ApiException;
use SubdomainTo\Exception\AuthenticationException;
use SubdomainTo\Exception\BadRequestException;
use SubdomainTo\Exception\ConflictException;
use SubdomainTo\Exception\ForbiddenException;
use SubdomainTo\Exception\NotFoundException;
use SubdomainTo\Exception\ServerException;
use SubdomainTo\Exception\TransportException;
use SubdomainTo\Resource\BillingResource;
use SubdomainTo\Resource\DomainZonesResource;
use SubdomainTo\Resource\HostnamesResource;
use SubdomainTo\Resource\OriginsResource;
use SubdomainTo\Resource\ProjectsResource;
use SubdomainTo\Resource\SystemResource;
use SubdomainTo\Resource\UsageResource;
use SubdomainTo\Resource\WebhooksResource;
use SubdomainTo\Resource\WidgetResource;

final class Client
{
    public const DEFAULT_BASE_URI = 'https://api.subdomain.to/v1';

    private $apiKey;
    private $httpClient;
    private $requestFactory;
    private $streamFactory;
    private $baseUri;

    public function __construct(string $apiKey, ClientInterface $httpClient, RequestFactoryInterface $requestFactory, StreamFactoryInterface $streamFactory, string $baseUri = self::DEFAULT_BASE_URI)
    {
        if (trim($apiKey) === '') { throw new \InvalidArgumentException('The subdomain.to API key cannot be empty.'); }
        if (filter_var($baseUri, FILTER_VALIDATE_URL) === false) { throw new \InvalidArgumentException('The subdomain.to base URI must be a valid absolute URL.'); }
        $this->apiKey = $apiKey;
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->baseUri = rtrim($baseUri, '/');
    }

    public function system(): SystemResource { return new SystemResource($this); }
    public function projects(): ProjectsResource { return new ProjectsResource($this); }
    public function origins(): OriginsResource { return new OriginsResource($this); }
    public function domainZones(): DomainZonesResource { return new DomainZonesResource($this); }
    public function hostnames(): HostnamesResource { return new HostnamesResource($this); }
    public function webhooks(): WebhooksResource { return new WebhooksResource($this); }
    public function widget(): WidgetResource { return new WidgetResource($this); }
    public function usage(): UsageResource { return new UsageResource($this); }
    public function billing(): BillingResource { return new BillingResource($this); }

    /** @internal */
    public function send(string $method, string $path, array $query = [], ?array $body = null, ?string $idempotencyKey = null, ?string $bearerToken = null, bool $authenticated = true): array
    {
        if ($idempotencyKey !== null && ($idempotencyKey === '' || strlen($idempotencyKey) > 128)) {
            throw new \InvalidArgumentException('Idempotency keys must contain between 1 and 128 characters.');
        }

        $query = array_filter($query, static function ($value): bool { return $value !== null; });
        $uri = $this->baseUri.'/'.ltrim($path, '/');
        if ($query !== []) { $uri .= '?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986); }
        $request = $this->requestFactory->createRequest(strtoupper($method), $uri)
            ->withHeader('Accept', 'application/json')
            ->withHeader('User-Agent', 'subdomainto-php/1.x');

        if ($authenticated) { $request = $request->withHeader('Authorization', 'Bearer '.($bearerToken ?? $this->apiKey)); }
        if ($idempotencyKey !== null) { $request = $request->withHeader('Idempotency-Key', $idempotencyKey); }
        if ($body !== null) {
            $json = json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
            $request = $request->withHeader('Content-Type', 'application/json')->withBody($this->streamFactory->createStream($json));
        }

        try { $response = $this->httpClient->sendRequest($request); }
        catch (ClientExceptionInterface $exception) { throw new TransportException('The subdomain.to request failed: '.$exception->getMessage(), 0, $exception); }

        $raw = (string) $response->getBody();
        $decoded = $raw === '' ? [] : json_decode($raw, true);
        if (!is_array($decoded)) { $decoded = []; }
        $status = $response->getStatusCode();
        if ($status >= 200 && $status < 300) { return $decoded; }

        $error = is_array($decoded['error'] ?? null) ? $decoded['error'] : [];
        $apiCode = isset($error['code']) ? (string) $error['code'] : 'http_error';
        $message = isset($error['message']) ? (string) $error['message'] : 'The subdomain.to API returned HTTP '.$status.'.';
        $requestId = isset($error['request_id']) ? (string) $error['request_id'] : ($response->getHeaderLine('X-Request-Id') ?: null);
        $class = $status === 400 ? BadRequestException::class
            : ($status === 401 ? AuthenticationException::class
            : ($status === 403 ? ForbiddenException::class
            : ($status === 404 ? NotFoundException::class
            : ($status === 409 ? ConflictException::class
            : ($status >= 500 ? ServerException::class : ApiException::class)))));
        throw new $class($status, $apiCode, $message, $requestId, $raw);
    }
}
