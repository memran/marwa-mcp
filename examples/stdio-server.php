<?php

declare(strict_types=1);

use Memran\MarwaMcp\Server\JsonRpcHandler;
use Memran\MarwaMcp\Server\ServerFactory;
use Memran\MarwaMcp\Transport\StdioTransport;

require __DIR__ . '/../vendor/autoload.php';

$transport = new StdioTransport(
    new JsonRpcHandler(ServerFactory::createDefault())
);

$transport->listen();
