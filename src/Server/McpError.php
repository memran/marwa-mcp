<?php

declare(strict_types=1);

namespace Memran\MarwaMcp\Server;

use RuntimeException;
use Throwable;

final class McpError extends RuntimeException
{
    public const PARSE_ERROR = -32700;
    public const INVALID_REQUEST = -32600;
    public const METHOD_NOT_FOUND = -32601;
    public const INVALID_PARAMS = -32602;
    public const INTERNAL_ERROR = -32603;
    public const PERMISSION_DENIED = -32001;

    public function __construct(
        private readonly int $rpcCode,
        string $message,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function rpcCode(): int
    {
        return $this->rpcCode;
    }
}
