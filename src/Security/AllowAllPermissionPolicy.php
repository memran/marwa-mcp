<?php

declare(strict_types=1);

namespace Memran\MarwaMcp\Security;

final class AllowAllPermissionPolicy implements PermissionPolicyInterface
{
    public function allowsMethod(string $method): bool
    {
        return true;
    }

    public function allowsTool(string $name): bool
    {
        return true;
    }

    public function allowsResource(string $uri): bool
    {
        return true;
    }

    public function allowsPrompt(string $name): bool
    {
        return true;
    }
}
