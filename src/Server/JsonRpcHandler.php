<?php

declare(strict_types=1);

namespace Memran\MarwaMcp\Server;

use JsonException;
use Memran\MarwaMcp\Support\Json;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

final class JsonRpcHandler
{
    public function __construct(
        private readonly McpServer $server,
        private readonly LoggerInterface $logger = new NullLogger()
    ) {
    }

    public function handle(string $json): string
    {
        try {
            $payload = Json::decode($json);
        } catch (JsonException) {
            return $this->encode(JsonRpcResponse::error(McpError::PARSE_ERROR, 'Parse error.')->toArray());
        }

        if (!is_array($payload)) {
            return $this->encode(JsonRpcResponse::error(McpError::INVALID_REQUEST, 'Invalid request.')->toArray());
        }

        if (array_is_list($payload)) {
            return $this->handleBatch($payload);
        }

        /** @var array<string, mixed> $payload */
        return $this->encode($this->handlePayload($payload)->toArray());
    }

    /**
     * @param array<int, mixed> $payloads
     */
    private function handleBatch(array $payloads): string
    {
        if ($payloads === []) {
            return $this->encode(JsonRpcResponse::error(McpError::INVALID_REQUEST, 'Invalid request.')->toArray());
        }

        $responses = [];
        foreach ($payloads as $payload) {
            if (!is_array($payload) || array_is_list($payload)) {
                $responses[] = JsonRpcResponse::error(McpError::INVALID_REQUEST, 'Invalid request.')->toArray();
                continue;
            }

            /** @var array<string, mixed> $payload */
            $responses[] = $this->handlePayload($payload)->toArray();
        }

        return $this->encode($responses);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handlePayload(array $payload): JsonRpcResponse
    {
        $id = isset($payload['id']) && (is_string($payload['id']) || is_int($payload['id'])) ? $payload['id'] : null;

        try {
            $request = JsonRpcRequest::fromArray($payload);
            return JsonRpcResponse::result($this->server->handle($request), $request->id);
        } catch (McpError $error) {
            if ($error->rpcCode() === McpError::INTERNAL_ERROR) {
                $this->logger->error($error->getMessage(), ['exception' => $error]);
            }

            return JsonRpcResponse::error($error->rpcCode(), $error->getMessage(), $id);
        } catch (Throwable $throwable) {
            $this->logger->error('Unhandled MCP exception.', ['exception' => $throwable]);
            return JsonRpcResponse::error(McpError::INTERNAL_ERROR, 'Internal error.', $id);
        }
    }

    /**
     * @param mixed $payload
     */
    private function encode(mixed $payload): string
    {
        try {
            return Json::encode($payload);
        } catch (JsonException $exception) {
            $this->logger->error('Failed to encode JSON-RPC response.', ['exception' => $exception]);
            return '{"jsonrpc":"2.0","id":null,"error":{"code":-32603,"message":"Internal error."}}';
        }
    }
}
