<?php

declare(strict_types=1);

namespace Memran\MarwaMcp\Server;

use Memran\MarwaMcp\Prompt\Examples\MarwaDebugHelpPrompt;
use Memran\MarwaMcp\Prompt\Examples\MarwaModuleGeneratorPrompt;
use Memran\MarwaMcp\Prompt\PromptRegistry;
use Memran\MarwaMcp\Resource\Examples\ServerInfoResource;
use Memran\MarwaMcp\Resource\Examples\ToolsResource;
use Memran\MarwaMcp\Resource\ResourceRegistry;
use Memran\MarwaMcp\Security\AllowAllPermissionPolicy;
use Memran\MarwaMcp\Security\PermissionPolicyInterface;
use Memran\MarwaMcp\Tool\Examples\EchoTool;
use Memran\MarwaMcp\Tool\Examples\PingTool;
use Memran\MarwaMcp\Tool\Examples\ServerInfoTool;
use Memran\MarwaMcp\Tool\ToolRegistry;

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
            $tools,
            $resources,
            $prompts,
            $permissionPolicy ?? new AllowAllPermissionPolicy(),
            $name,
            $version
        );
    }
}
