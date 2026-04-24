<?php

declare(strict_types=1);

namespace Memran\MarwaMcp\Tool\Examples;

use Memran\MarwaMcp\Support\Json;
use Memran\MarwaMcp\Tool\ToolInterface;
use Memran\MarwaMcp\Tool\ToolResult;

final readonly class ServerInfoTool implements ToolInterface
{
    public function __construct(private string $serverName, private string $serverVersion)
    {
    }

    public function name(): string
    {
        return 'server_info';
    }

    public function description(): string
    {
        return 'Returns server name, version, and PHP version.';
    }

    public function schema(): array
    {
        return ['type' => 'object', 'properties' => [], 'required' => []];
    }

    public function call(array $arguments): ToolResult
    {
        return ToolResult::text(Json::encode([
            'name' => $this->serverName,
            'version' => $this->serverVersion,
            'php' => PHP_VERSION,
        ]));
    }
}
