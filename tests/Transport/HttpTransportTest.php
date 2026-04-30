<?php

declare(strict_types=1);

namespace Marwa\MCP\Tests\Transport;

use Marwa\MCP\JsonRpcHandler;
use Marwa\MCP\ServerFactory;
use Marwa\MCP\HttpTransport;
use PHPUnit\Framework\TestCase;

final class HttpTransportTest extends TestCase
{
    public function testHttpPostReturnsJsonRpcResponse(): void
    {
        $transport = new HttpTransport(new JsonRpcHandler(ServerFactory::createDefault()));
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
        $transport = new HttpTransport(new JsonRpcHandler(ServerFactory::createDefault()));
        $response = $transport->handle('', 'GET');

        self::assertSame(405, $response['status']);
    }

    public function testHttpReturnsJsonRpcParseError(): void
    {
        $transport = new HttpTransport(new JsonRpcHandler(ServerFactory::createDefault()));
        $response = $transport->handle('{bad-json');
        $body = json_decode($response['body'], true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $response['status']);
        self::assertSame(-32700, $body['error']['code']);
    }
}
