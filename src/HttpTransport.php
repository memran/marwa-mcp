<?php

declare(strict_types=1);

namespace Marwa\MCP;

use Marwa\MCP\JsonRpcHandler;
use Marwa\MCP\TransportInterface;

final readonly class HttpTransport implements TransportInterface
{
    public function __construct(private JsonRpcHandler $handler)
    {
    }

    /**
     * @param array<string, string> $headers
     *
     * @return array{status:int,headers:array<string,string>,body:string}
     */
    public function handle(string $body, string $method = 'POST', array $headers = []): array
    {
        if (strtoupper($method) !== 'POST') {
            return [
                'status' => 405,
                'headers' => ['Content-Type' => 'application/json', 'Allow' => 'POST'],
                'body' => '{"jsonrpc":"2.0","id":null,"error":{"code":-32600,"message":"Only POST is allowed."}}',
            ];
        }

        return [
            'status' => 200,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => $this->handler->handle($body),
        ];
    }

    public function emit(): void
    {
        $response = $this->handle((string) file_get_contents('php://input'), $_SERVER['REQUEST_METHOD'] ?? 'POST');
        http_response_code($response['status']);
        foreach ($response['headers'] as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $response['body'];
    }

    public function listen(): void
    {
        $this->emit();
    }
}
