<?php

declare(strict_types=1);

namespace Memran\MarwaMcp\Marwa;

use Memran\MarwaMcp\Server\JsonRpcHandler;
use Memran\MarwaMcp\Server\ServerFactory;
use Memran\MarwaMcp\Transport\HttpTransport;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class McpServiceProvider
{
    public function register(object $container): void
    {
        $server = ServerFactory::createDefault();
        $logger = $this->resolveLogger($container);
        $handler = new JsonRpcHandler($server, $logger);
        $transport = new HttpTransport($handler);

        if (is_callable([$container, 'set'])) {
            call_user_func([$container, 'set'], 'marwa.mcp.server', $server);
            call_user_func([$container, 'set'], 'marwa.mcp.handler', $handler);
            call_user_func([$container, 'set'], 'marwa.mcp.http', $transport);
        }
    }

    private function resolveLogger(object $container): LoggerInterface
    {
        if (is_callable([$container, 'get'])) {
            $logger = call_user_func([$container, 'get'], LoggerInterface::class);
            if ($logger instanceof LoggerInterface) {
                return $logger;
            }
        }

        return new NullLogger();
    }
}
