<?php

declare(strict_types=1);

namespace Marwa\MCP;

use Marwa\MCP\McpError;

final class SchemaValidator
{
    /**
     * @param array<string, mixed> $schema
     * @param array<string, mixed> $value
     */
    public function validateObject(array $schema, array $value, string $label = 'value'): void
    {
        $type = $schema['type'] ?? 'object';
        if ($type !== 'object') {
            throw new McpError(McpError::INVALID_PARAMS, sprintf('Invalid %s schema.', $label));
        }

        $properties = $schema['properties'] ?? [];
        $required = $schema['required'] ?? [];

        if (!is_array($properties) || !is_array($required)) {
            throw new McpError(McpError::INVALID_PARAMS, sprintf('Invalid %s schema.', $label));
        }

        foreach ($required as $field) {
            if (is_string($field) && !array_key_exists($field, $value)) {
                throw new McpError(McpError::INVALID_PARAMS, sprintf('Missing required %s: %s.', $label, $field));
            }
        }

        foreach ($value as $name => $fieldValue) {
            $definition = $properties[$name] ?? null;
            if (!is_array($definition)) {
                throw new McpError(McpError::INVALID_PARAMS, sprintf('Unexpected %s: %s.', $label, $name));
            }

            $this->validateValue($definition, $fieldValue, sprintf('%s.%s', $label, $name));
        }
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function validateValue(array $schema, mixed $value, string $path): void
    {
        $type = $schema['type'] ?? null;
        if (is_string($type) && !$this->matchesType($value, $type)) {
            throw new McpError(McpError::INVALID_PARAMS, sprintf('Invalid type for %s.', $path));
        }

        if (isset($schema['enum']) && is_array($schema['enum']) && !in_array($value, $schema['enum'], true)) {
            throw new McpError(McpError::INVALID_PARAMS, sprintf('Invalid value for %s.', $path));
        }

        if (is_string($value)) {
            $this->validateString($schema, $value, $path);
        }

        if (is_array($value) && ($type === 'object' || $this->isAssoc($value))) {
            /** @var array<string, mixed> $value */
            $this->validateObject($schema, $value, $path);
        }
    }

    /**
     * @param array<string, mixed> $schema
     */
    private function validateString(array $schema, string $value, string $path): void
    {
        $length = strlen($value);

        if (isset($schema['minLength']) && is_int($schema['minLength']) && $length < $schema['minLength']) {
            throw new McpError(McpError::INVALID_PARAMS, sprintf('%s is too short.', $path));
        }

        if (isset($schema['maxLength']) && is_int($schema['maxLength']) && $length > $schema['maxLength']) {
            throw new McpError(McpError::INVALID_PARAMS, sprintf('%s is too long.', $path));
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
            'object' => is_array($value) && $this->isAssoc($value),
            'null' => is_null($value),
            default => true,
        };
    }

    /**
     * @param array<mixed> $value
     */
    private function isAssoc(array $value): bool
    {
        return $value === [] || !array_is_list($value);
    }
}
