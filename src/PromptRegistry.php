<?php

declare(strict_types=1);

namespace Marwa\MCP;

use Marwa\MCP\McpError;

final class PromptRegistry
{
    /** @var array<string, PromptInterface> */
    private array $prompts = [];

    public function register(PromptInterface $prompt): void
    {
        $name = $prompt->name();
        if (!preg_match('/^[a-zA-Z0-9_.-]{1,128}$/', $name)) {
            throw new McpError(McpError::INVALID_PARAMS, 'Invalid prompt name.');
        }

        $this->prompts[$name] = $prompt;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function list(): array
    {
        return array_values(array_map(
            static fn (PromptInterface $prompt): array => [
                'name' => $prompt->name(),
                'description' => $prompt->description(),
                'arguments' => $prompt->arguments(),
            ],
            $this->prompts
        ));
    }

    public function get(string $name): PromptInterface
    {
        return $this->prompts[$name] ?? throw new McpError(McpError::INVALID_PARAMS, 'Unknown prompt.');
    }
}
