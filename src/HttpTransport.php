<?php

declare(strict_types=1);

namespace Marwa\MCP;

use Marwa\MCP\JsonRpcHandler;
use Marwa\MCP\TransportInterface;

final readonly class HttpTransport implements TransportInterface
{
    private const DEFAULT_MAX_BODY_SIZE = 1_048_576;

    /**
     * @param list<string> $allowedOrigins Trusted origins for CORS. Empty = no CORS headers.
     */
    public function __construct(
        private JsonRpcHandler $handler,
        private ?AuthenticatorInterface $authenticator = null,
        private ?RateLimiterInterface $rateLimiter = null,
        private int $maxBodySize = self::DEFAULT_MAX_BODY_SIZE,
        private array $allowedOrigins = [],
        private bool $requireTls = false,
    ) {
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

        if ($this->requireTls && !$this->isSecure()) {
            return [
                'status' => 403,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => '{"jsonrpc":"2.0","id":null,"error":{"code":-32600,"message":"TLS required."}}',
            ];
        }

        if ($this->authenticator !== null && !$this->authenticator->authenticate($headers)) {
            return [
                'status' => 401,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => '{"jsonrpc":"2.0","id":null,"error":{"code":-32001,"message":"Unauthorized."}}',
            ];
        }

        if ($this->rateLimiter !== null && !$this->rateLimiter->allows($this->resolveClientIp($headers))) {
            return [
                'status' => 429,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => '{"jsonrpc":"2.0","id":null,"error":{"code":-32600,"message":"Rate limit exceeded."}}',
            ];
        }

        if (strlen($body) > $this->maxBodySize) {
            return [
                'status' => 413,
                'headers' => ['Content-Type' => 'application/json'],
                'body' => '{"jsonrpc":"2.0","id":null,"error":{"code":-32600,"message":"Request body too large."}}',
            ];
        }

        $responseHeaders = ['Content-Type' => 'application/json'];
        $corsHeader = $this->buildCorsHeader($headers);
        if ($corsHeader !== null) {
            $responseHeaders['Access-Control-Allow-Origin'] = $corsHeader;
            $responseHeaders['Access-Control-Allow-Methods'] = 'POST';
            $responseHeaders['Access-Control-Allow-Headers'] = 'Content-Type, Authorization';
        }

        return [
            'status' => 200,
            'headers' => $responseHeaders,
            'body' => $this->handler->handle($body),
        ];
    }

    public function emit(): void
    {
        $rawBody = file_get_contents('php://input');
        if ($rawBody === false) {
            $rawBody = '';
        }

        $method = $_SERVER['REQUEST_METHOD'] ?? 'POST';
        if (strtoupper($method) === 'OPTIONS') {
            http_response_code(204);
            header('Content-Type: application/json');
            header('Allow: POST, OPTIONS');

            return;
        }

        $response = $this->handle($rawBody, $method, $this->extractHeaders());
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

    private function isSecure(): bool
    {
        return (
            isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
        ) || (
            isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443
        ) || (
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'
        );
    }

    /**
     * @param array<string, string> $headers
     */
    private function resolveClientIp(array $headers): string
    {
        return $headers['x-forwarded-for'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * @param array<string, string> $headers
     */
    private function buildCorsHeader(array $headers): ?string
    {
        if ($this->allowedOrigins === []) {
            return null;
        }

        $origin = $headers['origin'] ?? '';
        if ($origin === '') {
            return null;
        }

        if (in_array('*', $this->allowedOrigins, true) || in_array($origin, $this->allowedOrigins, true)) {
            return $origin;
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    private function extractHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (is_string($value) && str_starts_with($key, 'HTTP_')) {
                $name = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$name] = $value;
            }
        }

        if (isset($_SERVER['CONTENT_TYPE']) && is_string($_SERVER['CONTENT_TYPE'])) {
            $headers['content-type'] = $_SERVER['CONTENT_TYPE'];
        }

        if (isset($_SERVER['REMOTE_ADDR']) && is_string($_SERVER['REMOTE_ADDR'])) {
            $headers['remote-addr'] = $_SERVER['REMOTE_ADDR'];
        }

        return $headers;
    }
}
