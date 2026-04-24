<?php

declare(strict_types=1);

namespace Memran\MarwaMcp\Prompt\Examples;

use Memran\MarwaMcp\Prompt\PromptInterface;
use Memran\MarwaMcp\Prompt\PromptResult;

final class MarwaModuleGeneratorPrompt implements PromptInterface
{
    public function name(): string
    {
        return 'marwa_module_generator';
    }

    public function description(): string
    {
        return 'Generate a concise Marwa module implementation plan.';
    }

    public function arguments(): array
    {
        return [
            ['name' => 'module', 'description' => 'Module name.', 'required' => true],
            ['name' => 'purpose', 'description' => 'Business purpose.', 'required' => false],
        ];
    }

    public function get(array $arguments): PromptResult
    {
        $module = isset($arguments['module']) && is_string($arguments['module']) ? $arguments['module'] : 'Module';
        $purpose = isset($arguments['purpose']) && is_string($arguments['purpose'])
            ? $arguments['purpose']
            : 'the requested feature';

        return PromptResult::userText(
            $this->description(),
            sprintf(
                'Create a Marwa module named %s for %s. Include routes, services, tests, and security checks.',
                $module,
                $purpose
            )
        );
    }
}
