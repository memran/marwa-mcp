# Marwa MCP

`memran/marwa-mcp` is a small PHP 8.3 library for building Model Context Protocol servers in Marwa Framework or standalone PHP apps.

It supports JSON-RPC 2.0, stdio, HTTP POST, tool/resource/prompt registries, permission checks, and PSR-3 logging.

## Installation

```bash
composer require memran/marwa-mcp
```

## Basic Stdio Usage

```bash
vendor/bin/marwa-mcp
```

Claude Desktop or Cursor can then start the server over stdio.

```json
{
  "mcpServers": {
    "marwa": {
      "command": "php",
      "args": ["vendor/bin/marwa-mcp"]
    }
  }
}
```

## HTTP Usage

```php
<?php

declare(strict_types=1);

use Memran\MarwaMcp\Server\JsonRpcHandler;
use Memran\MarwaMcp\Server\ServerFactory;
use Memran\MarwaMcp\Transport\HttpTransport;

require __DIR__ . '/vendor/autoload.php';

$server = ServerFactory::createDefault();
$transport = new HttpTransport(new JsonRpcHandler($server));
$transport->emit();
```

Example request:

```bash
curl -X POST http://localhost/mcp \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","id":1,"method":"tools/list","params":{}}'
```

## Marwa Controller Example

```php
<?php

declare(strict_types=1);

use Memran\MarwaMcp\Server\JsonRpcHandler;
use Memran\MarwaMcp\Server\ServerFactory;
use Memran\MarwaMcp\Transport\HttpTransport;

final class McpController
{
    public function __invoke(): void
    {
        $server = ServerFactory::createDefault();
        $transport = new HttpTransport(new JsonRpcHandler($server));
        $transport->emit();
    }
}
```

## Creating Custom Tools

```php
<?php

declare(strict_types=1);

use Memran\MarwaMcp\Tool\ToolInterface;
use Memran\MarwaMcp\Tool\ToolResult;

final class GreetingTool implements ToolInterface
{
    public function name(): string
    {
        return 'greeting';
    }

    public function description(): string
    {
        return 'Greets a user by name.';
    }

    public function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string'],
            ],
            'required' => ['name'],
        ];
    }

    public function call(array $arguments): ToolResult
    {
        return ToolResult::text('Hello ' . $arguments['name']);
    }
}
```

Register it:

```php
$server->tools()->register(new GreetingTool());
```

## Creating Resources

```php
<?php

declare(strict_types=1);

use Memran\MarwaMcp\Resource\ResourceInterface;
use Memran\MarwaMcp\Resource\ResourceResult;

final class StatusResource implements ResourceInterface
{
    public function uri(): string
    {
        return 'marwa://status';
    }

    public function name(): string
    {
        return 'Status';
    }

    public function description(): string
    {
        return 'Application status.';
    }

    public function read(): ResourceResult
    {
        return new ResourceResult($this->uri(), '{"ok":true}', 'application/json');
    }
}
```

## Creating Prompts

```php
<?php

declare(strict_types=1);

use Memran\MarwaMcp\Prompt\PromptInterface;
use Memran\MarwaMcp\Prompt\PromptResult;

final class ReviewPrompt implements PromptInterface
{
    public function name(): string
    {
        return 'review_code';
    }

    public function description(): string
    {
        return 'Ask the assistant to review code.';
    }

    public function arguments(): array
    {
        return [['name' => 'code', 'required' => true]];
    }

    public function get(array $arguments): PromptResult
    {
        return PromptResult::userText($this->description(), 'Review this code: ' . $arguments['code']);
    }
}
```

## Marwa Framework Integration

The optional provider is intentionally minimal and avoids framework lock-in:

```php
use Memran\MarwaMcp\Marwa\McpServiceProvider;

(new McpServiceProvider())->register($container);
```

It registers:

```text
marwa.mcp.server
marwa.mcp.handler
marwa.mcp.http
```

Your container must expose a `set(string $id, mixed $value): void` method. If it exposes `get(Psr\Log\LoggerInterface::class)`, that logger is used.

## Built-In Methods

Supported MCP methods:

```text
initialize
tools/list
tools/call
resources/list
resources/read
prompts/list
prompts/get
```

## Built-In Tools

```text
ping
server_info
echo
```

## Built-In Resources

```text
marwa://server/info
marwa://tools
```

## Built-In Prompts

```text
marwa_debug_help
marwa_module_generator
```

## Security Best Practices

Do not expose unsafe tools by default. This package intentionally ships without shell execution and raw SQL tools.

Use a custom `PermissionPolicyInterface` in production:

```php
use Memran\MarwaMcp\Security\PermissionPolicyInterface;

final class ProductionPolicy implements PermissionPolicyInterface
{
    public function allowsMethod(string $method): bool
    {
        return in_array($method, ['initialize', 'tools/list', 'tools/call'], true);
    }

    public function allowsTool(string $name): bool
    {
        return in_array($name, ['ping', 'server_info'], true);
    }

    public function allowsResource(string $uri): bool
    {
        return false;
    }

    public function allowsPrompt(string $name): bool
    {
        return false;
    }
}
```

Recommended production controls:

- Whitelist methods, tools, resources, and prompts.
- Validate every tool argument using the declared schema.
- Never put secrets into resource contents.
- Send logs to STDERR or a PSR-3 logger, never stdout for stdio MCP.
- Hide stack traces from client responses.
- Add authentication and audit logging at the HTTP boundary.

## Development

```bash
composer install
composer test
composer analyse
composer cs-check
```

## License

MIT. See [LICENSE](LICENSE).
