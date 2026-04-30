<?php

declare(strict_types=1);

namespace Marwa\MCP;

interface PermissionPolicyInterface
{
    public function allowsMethod(string $method): bool;

    public function allowsTool(string $name): bool;

    public function allowsResource(string $uri): bool;

    public function allowsPrompt(string $name): bool;
}
