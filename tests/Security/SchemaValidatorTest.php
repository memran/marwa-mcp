<?php

declare(strict_types=1);

namespace Memran\MarwaMcp\Tests\Security;

use Memran\MarwaMcp\Security\SchemaValidator;
use Memran\MarwaMcp\Server\McpError;
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
}
