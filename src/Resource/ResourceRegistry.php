<?php

declare(strict_types=1);

namespace Memran\MarwaMcp\Resource;

use Memran\MarwaMcp\Server\McpError;

final class ResourceRegistry
{
    /** @var array<string, ResourceInterface> */
    private array $resources = [];

    public function register(ResourceInterface $resource): void
    {
        $uri = $resource->uri();
        if (!filter_var($uri, FILTER_VALIDATE_URL) && !str_starts_with($uri, 'marwa://')) {
            throw new McpError(McpError::INVALID_PARAMS, 'Invalid resource URI.');
        }

        $this->resources[$uri] = $resource;
    }

    /**
     * @return list<array<string, string>>
     */
    public function list(): array
    {
        return array_values(array_map(
            static fn (ResourceInterface $resource): array => [
                'uri' => $resource->uri(),
                'name' => $resource->name(),
                'description' => $resource->description(),
            ],
            $this->resources
        ));
    }

    public function get(string $uri): ResourceInterface
    {
        return $this->resources[$uri] ?? throw new McpError(McpError::INVALID_PARAMS, 'Unknown resource.');
    }
}
