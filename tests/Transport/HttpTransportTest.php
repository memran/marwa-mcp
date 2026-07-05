<?php

declare(strict_types=1);

namespace Marwa\MCP\Tests\Transport;

use Marwa\MCP\AllowAllPermissionPolicy;
use Marwa\MCP\ApiKeyAuthenticator;
use Marwa\MCP\JsonRpcHandler;
use Marwa\MCP\ServerFactory;
use Marwa\MCP\HttpTransport;
use Marwa\MCP\SlidingWindowRateLimiter;
use PHPUnit\Framework\TestCase;

final class HttpTransportTest extends TestCase
{
    /**
     * @param array<string, mixed> $options
     */
    private function createTransport(array $options = []): HttpTransport
    {
        return new HttpTransport(
            handler: new JsonRpcHandler(ServerFactory::createDefault(new AllowAllPermissionPolicy())),
            authenticator: $options['authenticator'] ?? null,
            rateLimiter: $options['rateLimiter'] ?? null,
            maxBodySize: $options['maxBodySize'] ?? 1_048_576,
            allowedOrigins: $options['allowedOrigins'] ?? [],
            requireTls: $options['requireTls'] ?? false,
        );
    }

    public function testHttpPostReturnsJsonRpcResponse(): void
    {
        $transport = $this->createTransport();
        $response = $transport->handle(json_encode([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
        ], JSON_THROW_ON_ERROR));

        self::assertSame(200, $response['status']);
        self::assertSame('application/json', $response['headers']['Content-Type']);
        self::assertStringContainsString('serverInfo', $response['body']);
    }

    public function testHttpRejectsNonPost(): void
    {
        $transport = $this->createTransport();
        $response = $transport->handle('', 'GET');

        self::assertSame(405, $response['status']);
    }

    public function testHttpReturnsJsonRpcParseError(): void
    {
        $transport = $this->createTransport();
        $response = $transport->handle('{bad-json');
        $body = json_decode($response['body'], true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(400, $response['status']);
        self::assertSame(-32700, $body['error']['code']);
    }

    public function testHttpRejectsRequestWithoutAuth(): void
    {
        $transport = $this->createTransport([
            'authenticator' => new ApiKeyAuthenticator('test-secret'),
        ]);
        $response = $transport->handle(json_encode([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
        ], JSON_THROW_ON_ERROR));

        self::assertSame(401, $response['status']);
        self::assertStringContainsString('Unauthorized', $response['body']);
    }

    public function testHttpAcceptsRequestWithValidAuth(): void
    {
        $transport = $this->createTransport([
            'authenticator' => new ApiKeyAuthenticator('test-secret'),
        ]);
        $response = $transport->handle(
            json_encode([
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'initialize',
            ], JSON_THROW_ON_ERROR),
            'POST',
            ['authorization' => 'Bearer test-secret']
        );

        self::assertSame(200, $response['status']);
        self::assertStringContainsString('serverInfo', $response['body']);
    }

    public function testHttpRejectsRequestWithInvalidAuth(): void
    {
        $transport = $this->createTransport([
            'authenticator' => new ApiKeyAuthenticator('test-secret'),
        ]);
        $response = $transport->handle(
            json_encode([
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'initialize',
            ], JSON_THROW_ON_ERROR),
            'POST',
            ['authorization' => 'Bearer wrong-secret']
        );

        self::assertSame(401, $response['status']);
    }

    public function testHttpRejectsOversizedBody(): void
    {
        $transport = $this->createTransport(['maxBodySize' => 10]);
        $response = $transport->handle(str_repeat('x', 11));

        self::assertSame(413, $response['status']);
        self::assertStringContainsString('too large', $response['body']);
    }

    public function testHttpAcceptsBodyWithinSizeLimit(): void
    {
        $transport = $this->createTransport(['maxBodySize' => 1024]);
        $response = $transport->handle(json_encode([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
        ], JSON_THROW_ON_ERROR));

        self::assertSame(200, $response['status']);
    }

    public function testHttpRejectsWhenRateLimited(): void
    {
        $transport = $this->createTransport([
            'rateLimiter' => new SlidingWindowRateLimiter(maxRequests: 1, windowSeconds: 60),
        ]);

        $body = json_encode([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
        ], JSON_THROW_ON_ERROR);

        $response1 = $transport->handle($body, 'POST', ['remote-addr' => '127.0.0.1']);
        self::assertSame(200, $response1['status']);

        $response2 = $transport->handle($body, 'POST', ['remote-addr' => '127.0.0.1']);
        self::assertSame(429, $response2['status']);
    }

    public function testHttpReturnsCorsHeadersWhenOriginAllowed(): void
    {
        $transport = $this->createTransport(['allowedOrigins' => ['https://example.com']]);
        $response = $transport->handle(
            json_encode([
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'initialize',
            ], JSON_THROW_ON_ERROR),
            'POST',
            ['origin' => 'https://example.com']
        );

        self::assertSame(200, $response['status']);
        self::assertSame('https://example.com', $response['headers']['Access-Control-Allow-Origin']);
        self::assertArrayHasKey('Access-Control-Allow-Methods', $response['headers']);
    }

    public function testHttpOmitsCorsHeadersWhenOriginNotAllowed(): void
    {
        $transport = $this->createTransport(['allowedOrigins' => ['https://trusted.com']]);
        $response = $transport->handle(
            json_encode([
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'initialize',
            ], JSON_THROW_ON_ERROR),
            'POST',
            ['origin' => 'https://evil.com']
        );

        self::assertSame(200, $response['status']);
        self::assertArrayNotHasKey('Access-Control-Allow-Origin', $response['headers']);
    }

    public function testHttpOmitsCorsHeadersWhenNoOriginsConfigured(): void
    {
        $transport = $this->createTransport();
        $response = $transport->handle(
            json_encode([
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'initialize',
            ], JSON_THROW_ON_ERROR),
            'POST',
            ['origin' => 'https://example.com']
        );

        self::assertSame(200, $response['status']);
        self::assertArrayNotHasKey('Access-Control-Allow-Origin', $response['headers']);
    }

    public function testHttpRejectsWhenTlsRequiredButNotSecure(): void
    {
        $transport = $this->createTransport(['requireTls' => true]);
        $response = $transport->handle(json_encode([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
        ], JSON_THROW_ON_ERROR));

        self::assertSame(403, $response['status']);
        self::assertStringContainsString('TLS required', $response['body']);
    }

    public function testHttpReturns400OnParseError(): void
    {
        $transport = $this->createTransport();
        $response = $transport->handle('{bad-json');

        self::assertSame(400, $response['status']);
    }

    public function testHttpReturns404OnMethodNotFound(): void
    {
        $transport = $this->createTransport();
        $response = $transport->handle(json_encode([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'missing/method',
        ], JSON_THROW_ON_ERROR));

        self::assertSame(404, $response['status']);
    }

    public function testHttpReturns400OnInvalidParams(): void
    {
        $transport = $this->createTransport();
        $response = $transport->handle(json_encode([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => ['name' => 'echo', 'arguments' => ['text' => 123]],
        ], JSON_THROW_ON_ERROR));

        self::assertSame(400, $response['status']);
    }

    public function testHttpReturns200OnSuccess(): void
    {
        $transport = $this->createTransport();
        $response = $transport->handle(json_encode([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'initialize',
        ], JSON_THROW_ON_ERROR));

        self::assertSame(200, $response['status']);
    }
}
