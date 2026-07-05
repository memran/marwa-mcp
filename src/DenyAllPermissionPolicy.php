<?php

declare(strict_types=1);

namespace Marwa\MCP;

final class DenyAllPermissionPolicy implements PermissionPolicyInterface
{
    public function allowsMethod(string $method): bool
    {
        return false;
    }

    public function allowsTool(string $name): bool
    {
        return false;
    }

    public function allowsResource(string $uri): bool
    {
        return false;
    }

    public function allowsPrompt(string $name): bool
    {
        return false;
    }
}
