<?php

declare(strict_types=1);

namespace Marwa\MCP;

interface AuthenticatorInterface
{
    /**
     * @param array<string, string> $headers
     */
    public function authenticate(array $headers): bool;
}
