<?php

declare(strict_types=1);

namespace Marwa\MCP;

final class ApiKeyAuthenticator implements AuthenticatorInterface
{
    public function __construct(private readonly string $apiKey)
    {
    }

    /**
     * @param array<string, string> $headers
     */
    public function authenticate(array $headers): bool
    {
        $authorization = $headers['authorization'] ?? $headers['Authorization'] ?? '';

        if (!str_starts_with($authorization, 'Bearer ')) {
            return false;
        }

        $token = substr($authorization, 7);

        return hash_equals($this->apiKey, $token);
    }
}
