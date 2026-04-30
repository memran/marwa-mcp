<?php

declare(strict_types=1);

namespace Marwa\MCP\Tests\Server;

use Marwa\MCP\PermissionPolicyInterface;
use Marwa\MCP\JsonRpcHandler;
use Marwa\MCP\ServerFactory;
use PHPUnit\Framework\TestCase;

final class JsonRpcHandlerTest extends TestCase
{
    private JsonRpcHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new JsonRpcHandler(ServerFactory::createDefault());
    }

    public function testInitializeResponse(): void
    {
        $response = $this->request('initialize');

        self::assertSame('2.0', $response['jsonrpc']);
        self::assertSame('marwa-mcp', $response['result']['serverInfo']['name']);
        self::assertArrayHasKey('tools', $response['result']['capabilities']);
    }

    public function testInvalidMethod(): void
    {
        $response = $this->request('missing/method');

        self::assertSame(-32601, $response['error']['code']);
    }

    public function testInvalidJson(): void
    {
        $response = json_decode($this->handler->handle('{bad-json'), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(-32700, $response['error']['code']);
    }

    public function testPermissionDenied(): void
    {
        $policy = new class implements PermissionPolicyInterface {
            public function allowsMethod(string $method): bool
            {
                return $method !== 'tools/call';
            }

            public function allowsTool(string $name): bool
            {
                return false;
            }

            public function allowsResource(string $uri): bool
            {
                return true;
            }

            public function allowsPrompt(string $name): bool
            {
                return true;
            }
        };

        $handler = new JsonRpcHandler(ServerFactory::createDefault($policy));
        $response = json_decode($handler->handle(json_encode([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'tools/call',
            'params' => ['name' => 'ping', 'arguments' => []],
        ], JSON_THROW_ON_ERROR)), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(-32001, $response['error']['code']);
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    private function request(string $method, array $params = []): array
    {
        $response = $this->handler->handle(json_encode([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => $method,
            'params' => $params,
        ], JSON_THROW_ON_ERROR));

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        return $decoded;
    }
}
