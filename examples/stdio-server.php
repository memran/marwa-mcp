<?php

declare(strict_types=1);

use Marwa\MCP\JsonRpcHandler;
use Marwa\MCP\ServerFactory;
use Marwa\MCP\StdioTransport;

require __DIR__ . '/../vendor/autoload.php';

$transport = new StdioTransport(
    new JsonRpcHandler(ServerFactory::createDefault())
);

$transport->listen();
