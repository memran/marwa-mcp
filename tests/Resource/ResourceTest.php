<?php

declare(strict_types=1);

namespace Marwa\MCP\Tests\Resource;

use Marwa\MCP\JsonRpcHandler;
use Marwa\MCP\ServerFactory;
use PHPUnit\Framework\TestCase;

final class ResourceTest extends TestCase
{
    private JsonRpcHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new JsonRpcHandler(ServerFactory::createDefault());
    }

    public function testResourcesList(): void
    {
        $response = $this->request('resources/list');

        self::assertSame('marwa://server/info', $response['result']['resources'][0]['uri']);
    }

    public function testResourcesRead(): void
    {
        $response = $this->request('resources/read', ['uri' => 'marwa://server/info']);

        self::assertSame('application/json', $response['result']['contents'][0]['mimeType']);
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

        return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    }
}
