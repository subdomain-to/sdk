<?php

require dirname(__DIR__).'/vendor/autoload.php';

use GuzzleHttp\Client as Psr18Client;
use GuzzleHttp\Psr7\HttpFactory;
use SubdomainTo\Client;

$key = getenv('SUBDOMAINTO_SANDBOX_API_KEY');
$baseUri = getenv('SUBDOMAINTO_SANDBOX_BASE_URI');
if (!$key || !$baseUri) { fwrite(STDERR, "Sandbox credentials are required.\n"); exit(2); }
$factory = new HttpFactory();
$http = new Psr18Client();
$health = (new Client($key, $http, $factory, $factory, $baseUri))->system()->health();
if ($health->status() !== 'ok') { fwrite(STDERR, "Sandbox health check failed.\n"); exit(1); }
echo "Sandbox is healthy.\n";
