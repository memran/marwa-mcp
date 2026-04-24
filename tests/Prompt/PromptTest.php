<?php

declare(strict_types=1);

namespace Memran\MarwaMcp\Tests\Prompt;

use Memran\MarwaMcp\Server\JsonRpcHandler;
use Memran\MarwaMcp\Server\ServerFactory;
use PHPUnit\Framework\TestCase;

final class PromptTest extends TestCase
{
    private JsonRpcHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new JsonRpcHandler(ServerFactory::createDefault());
    }

    public function testPromptsList(): void
    {
        $response = $this->request('prompts/list');

        self::assertSame('marwa_debug_help', $response['result']['prompts'][0]['name']);
    }

    public function testPromptsGet(): void
    {
        $response = $this->request(
            'prompts/get',
            ['name' => 'marwa_debug_help', 'arguments' => ['issue' => 'route 404']]
        );

        self::assertStringContainsString('route 404', $response['result']['messages'][0]['content']['text']);
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
