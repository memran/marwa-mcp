<?php

declare(strict_types=1);

namespace Marwa\MCP;

use Marwa\MCP\ResourceInterface;
use Marwa\MCP\ResourceResult;
use Marwa\MCP\Json;

final readonly class ServerInfoResource implements ResourceInterface
{
    public function __construct(private string $serverName, private string $serverVersion)
    {
    }

    public function uri(): string
    {
        return 'marwa://server/info';
    }

    public function name(): string
    {
        return 'Server info';
    }

    public function description(): string
    {
        return 'Server name, package version, and PHP runtime version.';
    }

    public function read(): ResourceResult
    {
        return new ResourceResult($this->uri(), Json::encode([
            'name' => $this->serverName,
            'version' => $this->serverVersion,
            'php' => PHP_VERSION,
        ]), 'application/json');
    }
}
