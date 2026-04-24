<?php

declare(strict_types=1);

namespace Memran\MarwaMcp\Resource\Examples;

use Memran\MarwaMcp\Resource\ResourceInterface;
use Memran\MarwaMcp\Resource\ResourceResult;
use Memran\MarwaMcp\Support\Json;
use Memran\MarwaMcp\Tool\ToolRegistry;

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
