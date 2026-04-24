<?php

declare(strict_types=1);

namespace Memran\MarwaMcp\Tool;

interface ToolInterface
{
    public function name(): string;

    public function description(): string;

    /**
     * @return array<string, mixed>
     */
    public function schema(): array;

    /**
     * @param array<string, mixed> $arguments
     */
    public function call(array $arguments): ToolResult;
}
