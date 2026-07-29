# subdomain.to PHP SDK

`subdomainto/sdk` is the framework-independent, typed PHP SDK for adding customer custom domains to a SaaS product. It covers destinations, domain onboarding, DNS instructions, TLS and routing status, widget sessions, usage, billing, and signed webhook verification.

The client supports PHP 7.4 and later. It uses PSR-18 for HTTP requests and PSR-17 factories, so it works with the HTTP implementation already used by your application.

## Installation

Install the SDK with a PSR-18 client and PSR-17 implementation:

```bash
composer require subdomainto/sdk guzzlehttp/guzzle guzzlehttp/psr7
```

## Connect a customer domain

```php
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use SubdomainTo\Client;

$factory = new HttpFactory();
$client = new Client(
    $_ENV['SUBDOMAINTO_API_KEY'],
    new HttpClient(),
    $factory,
    $factory,
);

$destination = $client->destinations()->create(
    'https://app.example.com',
    'main-destination',
);

$domain = $client->domains()->create(
    'portal.customer.com',
    'customer-42-domain',
    $destination->id(),
);

foreach ($domain->dnsRecords() as $record) {
    printf("%s %s %s\n", $record->type(), $record->name(), $record->value());
}
```

Every idempotent creation method requires an explicit stable key. The SDK performs no implicit retries, leaving retry policy under your application's control.

## Verify signed webhooks

```php
use SubdomainTo\WebhookVerifier;

$event = (new WebhookVerifier())->verify(
    $rawBody,
    $_SERVER['HTTP_SUBDOMAINTO_SIGNATURE'] ?? '',
    $_ENV['SUBDOMAINTO_WEBHOOK_SECRET'],
);
```

The verifier checks the HMAC signature and timestamp before parsing the event. API failures extend `SubdomainTo\Exception\ApiException` and expose the HTTP status, API code, request ID, and original response body.

## Documentation

Read the complete [PHP SDK guide](https://docs.subdomain.to/sdks/php), browse the [custom domains API documentation](https://docs.subdomain.to), or report a problem in the [GitHub repository](https://github.com/subdomain-to/sdk/issues).

## License

MIT
