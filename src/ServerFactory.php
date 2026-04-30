<?php

declare(strict_types=1);

namespace Marwa\MCP;

use Marwa\MCP\MarwaDebugHelpPrompt;
use Marwa\MCP\MarwaModuleGeneratorPrompt;
use Marwa\MCP\PromptRegistry;
use Marwa\MCP\ServerInfoResource;
use Marwa\MCP\ToolsResource;
use Marwa\MCP\ResourceRegistry;
use Marwa\MCP\AllowAllPermissionPolicy;
use Marwa\MCP\PermissionPolicyInterface;
use Marwa\MCP\EchoTool;
use Marwa\MCP\PingTool;
use Marwa\MCP\ServerInfoTool;
use Marwa\MCP\ToolRegistry;

final class ServerFactory
{
    public static function createDefault(
        ?PermissionPolicyInterface $permissionPolicy = null,
        string $name = 'marwa-mcp',
        string $version = '0.1.0'
    ): McpServer {
        $tools = new ToolRegistry();
        $tools->register(new PingTool());
        $tools->register(new ServerInfoTool($name, $version));
        $tools->register(new EchoTool());

        $resources = new ResourceRegistry();
        $resources->register(new ServerInfoResource($name, $version));
        $resources->register(new ToolsResource($tools));

        $prompts = new PromptRegistry();
        $prompts->register(new MarwaDebugHelpPrompt());
        $prompts->register(new MarwaModuleGeneratorPrompt());

        return new McpServer(
            tools: $tools,
            resources: $resources,
            prompts: $prompts,
            permissionPolicy: $permissionPolicy ?? new AllowAllPermissionPolicy(),
            name: $name,
            version: $version
        );
    }
}
