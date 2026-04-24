<?php

declare(strict_types=1);

use Memran\MarwaMcp\Security\PermissionPolicyInterface;
use Memran\MarwaMcp\Server\JsonRpcHandler;
use Memran\MarwaMcp\Server\ServerFactory;
use Memran\MarwaMcp\Transport\StdioTransport;

require __DIR__ . '/../vendor/autoload.php';

final class ReadOnlyPolicy implements PermissionPolicyInterface
{
    public function allowsMethod(string $method): bool
    {
        return in_array($method, ['initialize', 'tools/list', 'resources/list', 'prompts/list'], true);
    }

    public function allowsTool(string $name): bool
    {
        return false;
    }

    public function allowsResource(string $uri): bool
    {
        return true;
    }

    public function allowsPrompt(string $name): bool
    {
        return true;
    }
}

$server = ServerFactory::createDefault(new ReadOnlyPolicy());

(new StdioTransport(new JsonRpcHandler($server)))->listen();
