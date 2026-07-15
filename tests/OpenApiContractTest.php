<?php

namespace SubdomainTo\Tests;

use PHPUnit\Framework\TestCase;

final class OpenApiContractTest extends TestCase
{
    public function testSdkOperationManifestMatchesTheCanonicalContract(): void
    {
        $file = getenv('SUBDOMAINTO_OPENAPI') ?: dirname(__DIR__, 3).'/openapi/openapi.yaml';
        self::assertFileExists($file, 'Set SUBDOMAINTO_OPENAPI when running outside the control-plane checkout.');
        preg_match_all('/^\s+operationId:\s*([A-Za-z0-9_]+)\s*$/m', (string) file_get_contents($file), $matches);
        $actual = $matches[1]; sort($actual);
        $expected = ['createCheckoutSession', 'createDomainZone', 'createHostname', 'createOrigin', 'createProject', 'createWebhookEndpoint', 'createWidgetHostname', 'createWidgetSession', 'deleteHostname', 'getHostname', 'getUsage', 'health', 'listHostnames', 'listProjects'];
        sort($expected);
        self::assertSame($expected, $actual);
    }
}
