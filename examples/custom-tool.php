<?php

declare(strict_types=1);

use Marwa\MCP\JsonRpcHandler;
use Marwa\MCP\ServerFactory;
use Marwa\MCP\ToolInterface;
use Marwa\MCP\ToolResult;
use Marwa\MCP\StdioTransport;

require __DIR__ . '/../vendor/autoload.php';

final class GreetingTool implements ToolInterface
{
    public function name(): string
    {
        return 'greeting';
    }

    public function description(): string
    {
        return 'Greets a user by name.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string', 'description' => 'Name to greet.'],
            ],
            'required' => ['name'],
        ];
    }

    public function call(array $arguments): ToolResult
    {
        return ToolResult::text('Hello ' . $arguments['name']);
    }
}

$server = ServerFactory::createDefault();
$server->tools()->register(new GreetingTool());

(new StdioTransport(new JsonRpcHandler($server)))->listen();
