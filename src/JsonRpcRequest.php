<?php

declare(strict_types=1);

namespace Marwa\MCP;

final readonly class JsonRpcRequest
{
    /**
     * @param array<string, mixed> $params
     */
    public function __construct(
        public string $method,
        public array $params = [],
        public string|int|null $id = null
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        if (($payload['jsonrpc'] ?? null) !== '2.0') {
            throw new McpError(McpError::INVALID_REQUEST, 'Invalid JSON-RPC version.');
        }

        if (!isset($payload['method']) || !is_string($payload['method']) || $payload['method'] === '') {
            throw new McpError(McpError::INVALID_REQUEST, 'Missing or invalid method.');
        }

        $params = $payload['params'] ?? [];
        if (!is_array($params)) {
            throw new McpError(McpError::INVALID_PARAMS, 'Params must be an object or array.');
        }

        $id = $payload['id'] ?? null;
        if (!is_null($id) && !is_string($id) && !is_int($id)) {
            throw new McpError(McpError::INVALID_REQUEST, 'Invalid request id.');
        }

        /** @var array<string, mixed> $params */
        return new self($payload['method'], $params, $id);
    }
}
