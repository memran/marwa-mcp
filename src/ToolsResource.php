<?php

declare(strict_types=1);

namespace Marwa\MCP;

use Marwa\MCP\ResourceInterface;
use Marwa\MCP\ResourceResult;
use Marwa\MCP\Json;
use Marwa\MCP\ToolRegistry;

final readonly class ToolsResource implements ResourceInterface
{
    public function __construct(private ToolRegistry $tools)
    {
    }

    public function uri(): string
    {
        return 'marwa://tools';
    }

    public function name(): string
    {
        return 'Registered tools';
    }

    public function description(): string
    {
        return 'JSON list of registered MCP tools.';
    }

    public function read(): ResourceResult
    {
        return new ResourceResult($this->uri(), Json::encode(['tools' => $this->tools->list()]), 'application/json');
    }
}
