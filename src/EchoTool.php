<?php

declare(strict_types=1);

namespace Marwa\MCP;

use Marwa\MCP\ToolInterface;
use Marwa\MCP\ToolResult;

final class EchoTool implements ToolInterface
{
    public function name(): string
    {
        return 'echo';
    }

    public function description(): string
    {
        return 'Returns the supplied text.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'text' => ['type' => 'string', 'description' => 'Text to echo.'],
            ],
            'required' => ['text'],
        ];
    }

    public function call(array $arguments): ToolResult
    {
        return ToolResult::text((string) $arguments['text']);
    }
}
