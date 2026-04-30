<?php

declare(strict_types=1);

namespace Marwa\MCP;

interface ResourceInterface
{
    public function uri(): string;

    public function name(): string;

    public function description(): string;

    public function read(): ResourceResult;
}
