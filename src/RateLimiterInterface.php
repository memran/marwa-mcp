<?php

declare(strict_types=1);

namespace Marwa\MCP;

interface RateLimiterInterface
{
    public function allows(string $key): bool;
}
