<?php

declare(strict_types=1);

namespace Memran\MarwaMcp\Prompt\Examples;

use Memran\MarwaMcp\Prompt\PromptInterface;
use Memran\MarwaMcp\Prompt\PromptResult;

final class MarwaDebugHelpPrompt implements PromptInterface
{
    public function name(): string
    {
        return 'marwa_debug_help';
    }

    public function description(): string
    {
        return 'Guide an assistant through debugging a Marwa Framework issue.';
    }

    public function arguments(): array
    {
        return [
            ['name' => 'issue', 'description' => 'The issue or error message.', 'required' => true],
        ];
    }

    public function get(array $arguments): PromptResult
    {
        $issue = isset($arguments['issue']) && is_string($arguments['issue'])
            ? $arguments['issue']
            : 'the reported issue';

        return PromptResult::userText(
            $this->description(),
            'Debug this Marwa issue. Identify likely causes, safe checks, and minimal fixes: '
                . $issue
        );
    }
}
