<?php

declare(strict_types=1);

namespace Marwa\MCP\Tests\Transport;

use Marwa\MCP\AllowAllPermissionPolicy;
use Marwa\MCP\ApiKeyAuthenticator;
use Marwa\MCP\JsonRpcHandler;
use Marwa\MCP\ServerFactory;
use Marwa\MCP\HttpTransport;
use PHPUnit\Framework\TestCase;

final class HttpTransportTest extends TestCase
{
    private function createTransport(): HttpTransport
    {
        return new HttpTransport(
            new JsonRpcHandler(ServerFactory::createDefault(new AllowAllPermissionPolicy()))
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

        self::assertSame(200, $response['status']);
        self::assertSame(-32700, $body['error']['code']);
    }

    public function testHttpRejectsRequestWithoutAuth(): void
    {
        $transport = new HttpTransport(
            new JsonRpcHandler(ServerFactory::createDefault(new AllowAllPermissionPolicy())),
            new ApiKeyAuthenticator('test-secret')
        );
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
        $transport = new HttpTransport(
            new JsonRpcHandler(ServerFactory::createDefault(new AllowAllPermissionPolicy())),
            new ApiKeyAuthenticator('test-secret')
        );
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
        $transport = new HttpTransport(
            new JsonRpcHandler(ServerFactory::createDefault(new AllowAllPermissionPolicy())),
            new ApiKeyAuthenticator('test-secret')
        );
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
}
