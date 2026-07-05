<?php

declare(strict_types=1);

namespace Marwa\MCP;

use Marwa\MCP\JsonRpcHandler;
use Marwa\MCP\ServerFactory;
use Marwa\MCP\HttpTransport;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class McpServiceProvider
{
    /**
     * @param list<string> $trustedProxies
     */
    public function register(
        object $container,
        ?PermissionPolicyInterface $permissionPolicy = null,
        ?AuthenticatorInterface $authenticator = null,
        ?RateLimiterInterface $rateLimiter = null,
        array $trustedProxies = [],
    ): void {
        $server = ServerFactory::createDefault(
            $permissionPolicy ?? new AllowAllPermissionPolicy()
        );
        $logger = $this->resolveLogger($container);
        $handler = new JsonRpcHandler($server, $logger);
        $transport = new HttpTransport(
            handler: $handler,
            authenticator: $authenticator,
            rateLimiter: $rateLimiter,
            trustedProxies: $trustedProxies,
        );

        if (is_callable([$container, 'set'])) {
            call_user_func([$container, 'set'], 'marwa.mcp.server', $server);
            call_user_func([$container, 'set'], 'marwa.mcp.handler', $handler);
            call_user_func([$container, 'set'], 'marwa.mcp.http', $transport);
        }
    }

    private function resolveLogger(object $container): LoggerInterface
    {
        if (is_callable([$container, 'get'])) {
            try {
                $logger = call_user_func([$container, 'get'], LoggerInterface::class);
                if ($logger instanceof LoggerInterface) {
                    return $logger;
                }
            } catch (\Throwable) {
                // Fall through to NullLogger
            }
        }

        return new NullLogger();
    }
}
