<?php

declare(strict_types=1);

namespace Marwa\MCP;

use Marwa\MCP\McpError;

final class ToolRegistry
{
    /** @var array<string, ToolInterface> */
    private array $tools = [];

    public function register(ToolInterface $tool): void
    {
        $name = $tool->name();
        if (!preg_match('/^[a-zA-Z0-9_.-]{1,128}$/', $name)) {
            throw new McpError(McpError::INVALID_PARAMS, 'Invalid tool name.');
        }

        $this->tools[$name] = $tool;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function list(): array
    {
        return array_values(array_map(
            static fn (ToolInterface $tool): array => [
                'name' => $tool->name(),
                'description' => $tool->description(),
                'inputSchema' => $tool->schema(),
            ],
            $this->tools
        ));
    }

    public function get(string $name): ToolInterface
    {
        return $this->tools[$name] ?? throw new McpError(McpError::INVALID_PARAMS, 'Unknown tool.');
    }
}
