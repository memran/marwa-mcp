<?php

declare(strict_types=1);

namespace Memran\MarwaMcp\Tool;

use Memran\MarwaMcp\Server\McpError;

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

    /**
     * @param array<string, mixed> $arguments
     */
    public function validateArguments(ToolInterface $tool, array $arguments): void
    {
        $schema = $tool->schema();
        $properties = $schema['properties'] ?? [];
        $required = $schema['required'] ?? [];

        if (!is_array($properties) || !is_array($required)) {
            throw new McpError(McpError::INVALID_PARAMS, 'Invalid tool schema.');
        }

        foreach ($required as $field) {
            if (is_string($field) && !array_key_exists($field, $arguments)) {
                throw new McpError(McpError::INVALID_PARAMS, sprintf('Missing required argument: %s.', $field));
            }
        }

        foreach ($arguments as $name => $value) {
            $definition = $properties[$name] ?? null;
            if (!is_array($definition)) {
                throw new McpError(McpError::INVALID_PARAMS, sprintf('Unexpected argument: %s.', $name));
            }

            $type = $definition['type'] ?? null;
            if (is_string($type) && !$this->matchesType($value, $type)) {
                throw new McpError(McpError::INVALID_PARAMS, sprintf('Invalid argument type: %s.', $name));
            }
        }
    }

    private function matchesType(mixed $value, string $type): bool
    {
        return match ($type) {
            'string' => is_string($value),
            'integer' => is_int($value),
            'number' => is_int($value) || is_float($value),
            'boolean' => is_bool($value),
            'array' => is_array($value) && array_is_list($value),
            'object' => is_array($value),
            'null' => is_null($value),
            default => true,
        };
    }
}
