<?php

declare(strict_types=1);

namespace Marwa\MCP\Tests\Security;

use Marwa\MCP\SchemaValidator;
use Marwa\MCP\McpError;
use PHPUnit\Framework\TestCase;

final class SchemaValidatorTest extends TestCase
{
    public function testValidatesNestedObjects(): void
    {
        $validator = new SchemaValidator();

        $validator->validateObject([
            'type' => 'object',
            'properties' => [
                'user' => [
                    'type' => 'object',
                    'properties' => [
                        'name' => ['type' => 'string', 'minLength' => 2],
                    ],
                    'required' => ['name'],
                ],
            ],
            'required' => ['user'],
        ], ['user' => ['name' => 'Marwa']], 'argument');

        self::assertTrue(true);
    }

    public function testRejectsInvalidEnumValue(): void
    {
        $validator = new SchemaValidator();

        $this->expectException(McpError::class);
        $this->expectExceptionMessage('Invalid value for argument.mode.');

        $validator->validateObject([
            'type' => 'object',
            'properties' => [
                'mode' => ['type' => 'string', 'enum' => ['safe']],
            ],
            'required' => ['mode'],
        ], ['mode' => 'unsafe'], 'argument');
    }

    public function testRejectsTooLongString(): void
    {
        $validator = new SchemaValidator();

        $this->expectException(McpError::class);
        $this->expectExceptionMessage('argument.text is too long.');

        $validator->validateObject([
            'type' => 'object',
            'properties' => [
                'text' => ['type' => 'string', 'maxLength' => 3],
            ],
            'required' => ['text'],
        ], ['text' => 'hello'], 'argument');
    }

    public function testRejectsUnknownType(): void
    {
        $validator = new SchemaValidator();

        $this->expectException(McpError::class);
        $this->expectExceptionMessage('Invalid type for argument.data.');

        $validator->validateObject([
            'type' => 'object',
            'properties' => [
                'data' => ['type' => 'malicious'],
            ],
            'required' => ['data'],
        ], ['data' => 'anything'], 'argument');
    }

    public function testRejectsExcessiveNesting(): void
    {
        $validator = new SchemaValidator(maxDepth: 2);

        $this->expectException(McpError::class);
        $this->expectExceptionMessage('Maximum nesting depth exceeded');

        $validator->validateObject([
            'type' => 'object',
            'properties' => [
                'level1' => [
                    'type' => 'object',
                    'properties' => [
                        'level2' => [
                            'type' => 'object',
                            'properties' => [
                                'level3' => ['type' => 'string'],
                            ],
                        ],
                    ],
                ],
            ],
            'required' => ['level1'],
        ], ['level1' => ['level2' => ['level3' => 'deep']]], 'argument');
    }

    public function testAllowsNestingWithinLimit(): void
    {
        $validator = new SchemaValidator(maxDepth: 3);

        $validator->validateObject([
            'type' => 'object',
            'properties' => [
                'level1' => [
                    'type' => 'object',
                    'properties' => [
                        'level2' => ['type' => 'string'],
                    ],
                ],
            ],
            'required' => ['level1'],
        ], ['level1' => ['level2' => 'ok']], 'argument');

        self::assertTrue(true);
    }
}
