<?php

declare(strict_types=1);

use Marwa\MCP\JsonRpcHandler;
use Marwa\MCP\ServerFactory;
use Marwa\MCP\HttpTransport;

require __DIR__ . '/../../../vendor/autoload.php';

$transport = new HttpTransport(
    new JsonRpcHandler(ServerFactory::createDefault())
);

$transport->emit();
