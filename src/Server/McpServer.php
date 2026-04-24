<?php

declare(strict_types=1);

namespace Memran\MarwaMcp\Server;

use Memran\MarwaMcp\Prompt\PromptRegistry;
use Memran\MarwaMcp\Resource\ResourceRegistry;
use Memran\MarwaMcp\Security\AllowAllPermissionPolicy;
use Memran\MarwaMcp\Security\PermissionPolicyInterface;
use Memran\MarwaMcp\Tool\ToolRegistry;

final class McpServer
{
    public function __construct(
        private readonly ToolRegistry $tools,
        private readonly ResourceRegistry $resources,
        private readonly PromptRegistry $prompts,
        private readonly PermissionPolicyInterface $permissionPolicy = new AllowAllPermissionPolicy(),
        private readonly string $name = 'marwa-mcp',
        private readonly string $version = '0.1.0'
    ) {
    }

    public function tools(): ToolRegistry
    {
        return $this->tools;
    }

    public function resources(): ResourceRegistry
    {
        return $this->resources;
    }

    public function prompts(): PromptRegistry
    {
        return $this->prompts;
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(JsonRpcRequest $request): array
    {
        if (!$this->permissionPolicy->allowsMethod($request->method)) {
            throw new McpError(McpError::PERMISSION_DENIED, 'Permission denied.');
        }

        return match ($request->method) {
            'initialize' => $this->initialize(),
            'tools/list' => ['tools' => $this->tools->list()],
            'tools/call' => $this->callTool($request->params),
            'resources/list' => ['resources' => $this->resources->list()],
            'resources/read' => $this->readResource($request->params),
            'prompts/list' => ['prompts' => $this->prompts->list()],
            'prompts/get' => $this->getPrompt($request->params),
            default => throw new McpError(McpError::METHOD_NOT_FOUND, 'Method not found.'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function initialize(): array
    {
        return [
            'protocolVersion' => '2024-11-05',
            'serverInfo' => [
                'name' => $this->name,
                'version' => $this->version,
            ],
            'capabilities' => [
                'tools' => new \stdClass(),
                'resources' => new \stdClass(),
                'prompts' => new \stdClass(),
            ],
        ];
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    private function callTool(array $params): array
    {
        $name = $params['name'] ?? null;
        if (!is_string($name) || $name === '') {
            throw new McpError(McpError::INVALID_PARAMS, 'Tool name is required.');
        }

        if (!$this->permissionPolicy->allowsTool($name)) {
            throw new McpError(McpError::PERMISSION_DENIED, 'Permission denied.');
        }

        $arguments = $params['arguments'] ?? [];
        if (!is_array($arguments) || ($arguments !== [] && array_is_list($arguments))) {
            throw new McpError(McpError::INVALID_PARAMS, 'Tool arguments must be an object.');
        }

        /** @var array<string, mixed> $arguments */
        $tool = $this->tools->get($name);
        $this->tools->validateArguments($tool, $arguments);

        return $tool->call($arguments)->toArray();
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    private function readResource(array $params): array
    {
        $uri = $params['uri'] ?? null;
        if (!is_string($uri) || $uri === '') {
            throw new McpError(McpError::INVALID_PARAMS, 'Resource URI is required.');
        }

        if (!$this->permissionPolicy->allowsResource($uri)) {
            throw new McpError(McpError::PERMISSION_DENIED, 'Permission denied.');
        }

        return $this->resources->get($uri)->read()->toArray();
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    private function getPrompt(array $params): array
    {
        $name = $params['name'] ?? null;
        if (!is_string($name) || $name === '') {
            throw new McpError(McpError::INVALID_PARAMS, 'Prompt name is required.');
        }

        if (!$this->permissionPolicy->allowsPrompt($name)) {
            throw new McpError(McpError::PERMISSION_DENIED, 'Permission denied.');
        }

        $arguments = $params['arguments'] ?? [];
        if (!is_array($arguments) || ($arguments !== [] && array_is_list($arguments))) {
            throw new McpError(McpError::INVALID_PARAMS, 'Prompt arguments must be an object.');
        }

        /** @var array<string, mixed> $arguments */
        return $this->prompts->get($name)->get($arguments)->toArray();
    }
}
