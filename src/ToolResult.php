<?php

declare(strict_types=1);

namespace Marwa\MCP;

final readonly class ToolResult
{
    /**
     * @param array<int, array<string, mixed>> $content
     */
    public function __construct(private array $content, private bool $isError = false)
    {
    }

    public static function text(string $text, bool $isError = false): self
    {
        return new self([['type' => 'text', 'text' => $text]], $isError);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['content' => $this->content, 'isError' => $this->isError];
    }
}
