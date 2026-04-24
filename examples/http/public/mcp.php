<?php

declare(strict_types=1);

use Memran\MarwaMcp\Server\JsonRpcHandler;
use Memran\MarwaMcp\Server\ServerFactory;
use Memran\MarwaMcp\Transport\HttpTransport;

require __DIR__ . '/../../../vendor/autoload.php';

$transport = new HttpTransport(
    new JsonRpcHandler(ServerFactory::createDefault())
);

$transport->emit();
