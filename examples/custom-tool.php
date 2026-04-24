<?php

declare(strict_types=1);

use Memran\MarwaMcp\Server\JsonRpcHandler;
use Memran\MarwaMcp\Server\ServerFactory;
use Memran\MarwaMcp\Tool\ToolInterface;
use Memran\MarwaMcp\Tool\ToolResult;
use Memran\MarwaMcp\Transport\StdioTransport;

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
