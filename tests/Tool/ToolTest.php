<?php

declare(strict_types=1);

namespace Memran\MarwaMcp\Tests\Tool;

use Memran\MarwaMcp\Server\JsonRpcHandler;
use Memran\MarwaMcp\Server\ServerFactory;
use PHPUnit\Framework\TestCase;

final class ToolTest extends TestCase
{
    private JsonRpcHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new JsonRpcHandler(ServerFactory::createDefault());
    }

    public function testToolsList(): void
    {
        $response = $this->request('tools/list');

        self::assertCount(3, $response['result']['tools']);
        self::assertSame('ping', $response['result']['tools'][0]['name']);
    }

    public function testToolsCall(): void
    {
        $response = $this->request('tools/call', ['name' => 'echo', 'arguments' => ['text' => 'hello']]);

        self::assertSame('hello', $response['result']['content'][0]['text']);
    }

    public function testToolArgumentValidation(): void
    {
        $response = $this->request('tools/call', ['name' => 'echo', 'arguments' => ['text' => 123]]);

        self::assertSame(-32602, $response['error']['code']);
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
