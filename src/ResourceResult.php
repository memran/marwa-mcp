<?php

declare(strict_types=1);

namespace Marwa\MCP;

final readonly class ResourceResult
{
    public function __construct(
        private string $uri,
        private string $text,
        private string $mimeType = 'text/plain'
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'contents' => [
                [
                    'uri' => $this->uri,
                    'mimeType' => $this->mimeType,
                    'text' => $this->text,
                ],
            ],
        ];
    }
}
