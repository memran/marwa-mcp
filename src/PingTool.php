<?php

declare(strict_types=1);

namespace Marwa\MCP;

use Marwa\MCP\ToolInterface;
use Marwa\MCP\ToolResult;

final class PingTool implements ToolInterface
{
    public function name(): string
    {
        return 'ping';
    }

    public function description(): string
    {
        return 'Returns pong.';
    }

    public function schema(): array
    {
        return ['type' => 'object', 'properties' => [], 'required' => []];
    }

    public function call(array $arguments): ToolResult
    {
        return ToolResult::text('pong');
    }
}
