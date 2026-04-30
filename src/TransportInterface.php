<?php

declare(strict_types=1);

namespace Marwa\MCP;

interface TransportInterface
{
    public function listen(): void;
}
