<?php

declare(strict_types=1);

namespace Memran\MarwaMcp\Tests\Transport;

use Memran\MarwaMcp\Server\JsonRpcHandler;
use Memran\MarwaMcp\Server\ServerFactory;
use Memran\MarwaMcp\Transport\HttpTransport;
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
}
